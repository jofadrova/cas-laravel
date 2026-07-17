<?php

namespace App\Services;

use App\Models\AmortizacionCapital;
use App\Models\CuotaSolicitud;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AmortizacionCapitalService
{
    public function simular(Prestamo $prestamo, float $montoCapital, string $tipoRecalculo): array
    {
        $pendientes = $prestamo->cuotas()
            ->where('estado', 'PE')
            ->orderBy('nro_cuota')
            ->get();

        return $this->calcular($prestamo, $pendientes, $montoCapital, $tipoRecalculo);
    }

    public function aplicar(Prestamo $prestamo, array $datos): AmortizacionCapital
    {
        return DB::transaction(function () use ($prestamo, $datos) {
            $prestamoBloqueado = Prestamo::query()
                ->with('tipo')
                ->whereKey($prestamo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $pendientes = $prestamoBloqueado->cuotas()
                ->where('estado', 'PE')
                ->orderBy('nro_cuota')
                ->lockForUpdate()
                ->get();

            if ($prestamoBloqueado->estado !== 'AC' || $pendientes->isEmpty()) {
                throw ValidationException::withMessages([
                    'monto_efectivo' => 'El préstamo no está activo o no tiene cuotas pendientes.',
                ]);
            }

            $esDolares = $prestamoBloqueado->tipo->tipo_moneda === 'SU';
            $montoEfectivo = round((float) $datos['monto_efectivo'], 2);
            $tipoCambio = $esDolares ? round((float) ($datos['tipo_cambio'] ?? 0), 5) : null;

            if ($esDolares && $tipoCambio <= 0) {
                throw ValidationException::withMessages([
                    'tipo_cambio' => 'Debe ingresar un tipo de cambio válido.',
                ]);
            }

            $montoCapital = $esDolares
                ? round($montoEfectivo / $tipoCambio, 2)
                : $montoEfectivo;

            $simulacion = $this->calcular(
                $prestamoBloqueado,
                $pendientes,
                $montoCapital,
                $datos['tipo_recalculo']
            );

            foreach ($simulacion['cronograma'] as $fila) {
                CuotaSolicitud::query()
                    ->whereKey($fila['id'])
                    ->update([
                        'cuota_fija' => $fila['cuota_fija'],
                        'amortizacion_cap' => $fila['amortizacion_cap'],
                        'interes' => $fila['interes'],
                        'min_defensa' => $fila['min_defensa'],
                        'capital_amortizado' => $fila['amortizacion_cap'],
                        'saldo' => $fila['saldo'],
                    ]);
            }

            $idsConservados = collect($simulacion['cronograma'])->pluck('id');
            $idsEliminados = $pendientes->pluck('id')->diff($idsConservados);

            if ($idsEliminados->isNotEmpty()) {
                CuotaSolicitud::query()
                    ->whereIn('id', $idsEliminados)
                    ->where('estado', 'PE')
                    ->delete();
            }

            $pago = Pago::create([
                'monto' => $montoEfectivo,
                'diferencia' => 0,
                'tipo_moneda' => 'B',
                'tipo_cambio' => $tipoCambio,
                'fecha' => now()->toDateString(),
                'tipo_pago' => 'AM',
                'anexo' => $datos['observaciones'] ?: 'Amortización de capital',
                'estado' => 'AC',
            ]);

            $amortizacion = AmortizacionCapital::create([
                'id_solicitud' => $prestamoBloqueado->getKey(),
                'id_pago' => $pago->getKey(),
                'tipo_recalculo' => $datos['tipo_recalculo'],
                'monto_efectivo' => $montoEfectivo,
                'monto_capital' => $montoCapital,
                'tipo_cambio' => $tipoCambio,
                'saldo_anterior' => $simulacion['saldo_anterior'],
                'saldo_nuevo' => $simulacion['saldo_nuevo'],
                'cuota_anterior' => $simulacion['cuota_anterior'],
                'cuota_nueva' => $simulacion['cuota_nueva'],
                'periodo_anterior' => $simulacion['periodo_anterior'],
                'periodo_nuevo' => $simulacion['periodo_nuevo'],
                'autorizacion' => $datos['autorizacion'],
                'observaciones' => $datos['observaciones'] ?: null,
                'fecha' => now()->toDateString(),
                'estado' => 'AC',
            ]);

            $prestamoBloqueado->update([
                'saldo_actual' => $simulacion['saldo_nuevo'],
                'periodo' => $simulacion['periodo_nuevo'],
            ]);

            return $amortizacion;
        });
    }

    private function calcular(
        Prestamo $prestamo,
        Collection $pendientes,
        float $montoCapital,
        string $tipoRecalculo
    ): array {
        $saldoAnterior = round((float) $prestamo->saldo_actual, 2);
        $montoCapital = round($montoCapital, 2);

        if ($pendientes->isEmpty()) {
            throw ValidationException::withMessages([
                'monto_efectivo' => 'El préstamo no tiene cuotas pendientes.',
            ]);
        }

        if ($montoCapital <= 0 || $montoCapital >= $saldoAnterior) {
            throw ValidationException::withMessages([
                'monto_efectivo' => 'La amortización debe ser mayor a cero y menor al saldo actual.',
            ]);
        }

        if (! in_array($tipoRecalculo, ['CUOTA', 'PLAZO'], true)) {
            throw ValidationException::withMessages([
                'tipo_recalculo' => 'El tipo de recálculo no es válido.',
            ]);
        }

        $saldoNuevo = round($saldoAnterior - $montoCapital, 2);
        $tasaMensual = (float) $prestamo->interes / 100;
        $primeraCuota = $pendientes->first();
        $cuotaTotalActual = round((float) $primeraCuota->cuota_fija, 2);

        if ($cuotaTotalActual <= 0) {
            throw ValidationException::withMessages([
                'monto_efectivo' => 'La cuota vigente no permite recalcular el cronograma.',
            ]);
        }

        $cronograma = $tipoRecalculo === 'CUOTA'
            ? $this->reducirCuota($prestamo, $pendientes, $saldoNuevo, $tasaMensual)
            : $this->reducirPlazo($pendientes, $saldoNuevo, $tasaMensual, $cuotaTotalActual);

        $ultimaFila = collect($cronograma)->last();

        return [
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'monto_capital' => $montoCapital,
            'cuota_anterior' => round((float) $primeraCuota->cuota_fija, 2),
            'cuota_nueva' => $cronograma[0]['cuota_fija'],
            'periodo_anterior' => (int) $prestamo->periodo,
            'periodo_nuevo' => (int) $ultimaFila['nro_cuota'],
            'cuotas_restantes_anterior' => $pendientes->count(),
            'cuotas_restantes_nuevo' => count($cronograma),
            'cronograma' => $cronograma,
        ];
    }

    private function reducirCuota(
        Prestamo $prestamo,
        Collection $pendientes,
        float $saldo,
        float $tasaMensual
    ): array {
        $numeroCuotas = $pendientes->count();
        $cuotaBase = $tasaMensual > 0
            ? $saldo * (($tasaMensual * pow(1 + $tasaMensual, $numeroCuotas))
                / (pow(1 + $tasaMensual, $numeroCuotas) - 1))
            : $saldo / $numeroCuotas;

        return $this->construirCronograma(
            $prestamo,
            $pendientes,
            $saldo,
            $tasaMensual,
            $cuotaBase,
            $numeroCuotas
        );
    }

    private function reducirPlazo(
        Collection $pendientes,
        float $saldo,
        float $tasaMensual,
        float $cuotaTotalActual
    ): array {
        $saldoTemporal = $saldo;
        $cronograma = [];

        foreach ($pendientes as $cuotaOriginal) {
            $interes = round($saldoTemporal * $tasaMensual, 2);
            $cargos =
                (float) $cuotaOriginal->min_defensa
                + (float) $cuotaOriginal->itf
                + (float) $cuotaOriginal->papel
                + (float) $cuotaOriginal->comision_mindef
                + (float) $cuotaOriginal->rep_formulario;
            $capital = round($cuotaTotalActual - $interes - $cargos, 2);

            if ($capital <= 0) {
                throw ValidationException::withMessages([
                    'monto_efectivo' => 'La cuota actual no cubre los intereses y no permite reducir el plazo.',
                ]);
            }

            $capital = min($capital, $saldoTemporal);
            $saldoTemporal = max(0, round($saldoTemporal - $capital, 2));

            $cronograma[] = [
                'id' => $cuotaOriginal->id,
                'nro_cuota' => (int) $cuotaOriginal->nro_cuota,
                'mes' => (int) $cuotaOriginal->mes,
                'gestion' => (int) $cuotaOriginal->gestion,
                'cuota_fija' => round($capital + $interes + $cargos, 2),
                'amortizacion_cap' => round($capital, 2),
                'interes' => $interes,
                'min_defensa' => round((float) $cuotaOriginal->min_defensa, 2),
                'saldo' => $saldoTemporal,
            ];

            if ($saldoTemporal <= 0) {
                break;
            }
        }

        if ($saldoTemporal > 0) {
            throw ValidationException::withMessages([
                'monto_efectivo' => 'No fue posible reducir el plazo con la cuota vigente.',
            ]);
        }

        if (count($cronograma) >= $pendientes->count()) {
            throw ValidationException::withMessages([
                'monto_efectivo' => 'El monto no alcanza para reducir al menos una cuota del plazo.',
            ]);
        }

        return $cronograma;
    }

    private function construirCronograma(
        Prestamo $prestamo,
        Collection $pendientes,
        float $saldoInicial,
        float $tasaMensual,
        float $cuotaBase,
        int $numeroCuotas
    ): array {
        $saldo = $saldoInicial;
        $minDefensa = round($cuotaBase * ((float) $prestamo->min_defensa / 100), 2);
        $cronograma = [];

        for ($indice = 0; $indice < $numeroCuotas; $indice++) {
            $cuotaOriginal = $pendientes[$indice];
            $interes = round($saldo * $tasaMensual, 2);
            $capital = round($cuotaBase - $interes, 2);

            if ($indice === $numeroCuotas - 1 || $capital > $saldo) {
                $capital = $saldo;
            }

            $saldo = max(0, round($saldo - $capital, 2));
            $otrosCargos =
                (float) $cuotaOriginal->itf
                + (float) $cuotaOriginal->papel
                + (float) $cuotaOriginal->comision_mindef
                + (float) $cuotaOriginal->rep_formulario;

            $cronograma[] = [
                'id' => $cuotaOriginal->id,
                'nro_cuota' => (int) $cuotaOriginal->nro_cuota,
                'mes' => (int) $cuotaOriginal->mes,
                'gestion' => (int) $cuotaOriginal->gestion,
                'cuota_fija' => round($capital + $interes + $minDefensa + $otrosCargos, 2),
                'amortizacion_cap' => round($capital, 2),
                'interes' => round($interes, 2),
                'min_defensa' => $minDefensa,
                'saldo' => $saldo,
            ];
        }

        return $cronograma;
    }
}
