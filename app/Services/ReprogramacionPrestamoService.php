<?php

namespace App\Services;

use App\Models\CuotaSolicitud;
use App\Models\Prestamo;
use App\Models\ReprogramacionPrestamo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReprogramacionPrestamoService
{
    public function aplicar(Prestamo $prestamo, array $datos): ReprogramacionPrestamo
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
            $pagadas = $prestamoBloqueado->cuotas()
                ->where('estado', 'AC')
                ->orderBy('nro_cuota')
                ->get();

            $nuevasPendientes = (int) $datos['cuotas_pendientes_nuevas'];
            $ultimaPagada = (int) ($pagadas->max('nro_cuota') ?? 0);

            $this->validar(
                $prestamoBloqueado,
                $pendientes,
                $nuevasPendientes,
                $ultimaPagada
            );

            $cronograma = $this->generarCronograma(
                $prestamoBloqueado,
                $pendientes,
                $nuevasPendientes,
                $ultimaPagada
            );

            $cuotaAnterior = round((float) $pendientes->first()->cuota_fija, 2);
            $periodoAnterior = (int) $prestamoBloqueado->periodo;
            $periodoNuevo = $ultimaPagada + $nuevasPendientes;

            $prestamoBloqueado->cuotas()
                ->where('estado', 'PE')
                ->delete();

            foreach ($cronograma as $fila) {
                CuotaSolicitud::create([
                    'id_solicitud' => $prestamoBloqueado->getKey(),
                    'nro_cuota' => $fila['nro_cuota'],
                    'mes' => $fila['mes'],
                    'gestion' => $fila['gestion'],
                    'cuota_fija' => $fila['cuota_fija'],
                    'amortizacion_cap' => $fila['amortizacion_cap'],
                    'interes' => $fila['interes'],
                    'min_defensa' => $fila['min_defensa'],
                    'itf' => $fila['itf'],
                    'papel' => $fila['papel'],
                    'capital_amortizado' => $fila['amortizacion_cap'],
                    'saldo' => $fila['saldo'],
                    'estado' => 'PE',
                    'comision_mindef' => $fila['comision_mindef'],
                    'rep_formulario' => $fila['rep_formulario'],
                ]);
            }

            $prestamoBloqueado->update([
                'periodo' => $periodoNuevo,
            ]);

            return ReprogramacionPrestamo::create([
                'id_solicitud' => $prestamoBloqueado->getKey(),
                'cuotas_pagadas' => $pagadas->count(),
                'cuotas_pendientes_anterior' => $pendientes->count(),
                'cuotas_pendientes_nuevo' => $nuevasPendientes,
                'periodo_anterior' => $periodoAnterior,
                'periodo_nuevo' => $periodoNuevo,
                'saldo_capital' => $prestamoBloqueado->saldo_actual,
                'cuota_anterior' => $cuotaAnterior,
                'cuota_nueva' => $cronograma[0]['cuota_fija'],
                'autorizacion' => $datos['autorizacion'],
                'observaciones' => $datos['observaciones'] ?: null,
                'fecha' => now()->toDateString(),
                'estado' => 'AC',
            ]);
        });
    }

    private function validar(
        Prestamo $prestamo,
        Collection $pendientes,
        int $nuevasPendientes,
        int $ultimaPagada
    ): void {
        if (
            $prestamo->estado !== 'AC'
            || (float) $prestamo->saldo_actual <= 0
            || $pendientes->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'cuotas_pendientes_nuevas' => 'El préstamo no está habilitado para reprogramación.',
            ]);
        }

        if ($nuevasPendientes <= $pendientes->count()) {
            throw ValidationException::withMessages([
                'cuotas_pendientes_nuevas' => 'La reprogramación debe aumentar las cuotas pendientes.',
            ]);
        }

        if ($ultimaPagada + $nuevasPendientes > (int) $prestamo->tipo->plazo_max) {
            throw ValidationException::withMessages([
                'cuotas_pendientes_nuevas' => 'El nuevo plazo supera el máximo permitido.',
            ]);
        }
    }

    private function generarCronograma(
        Prestamo $prestamo,
        Collection $pendientes,
        int $numeroCuotas,
        int $ultimaPagada
    ): array {
        $saldo = round((float) $prestamo->saldo_actual, 2);
        $tasaMensual = (float) $prestamo->interes / 100;
        $factor = pow(1 + $tasaMensual, $numeroCuotas);
        $cuotaBase = $tasaMensual > 0
            ? $saldo * (($tasaMensual * $factor) / ($factor - 1))
            : $saldo / $numeroCuotas;
        $minDefensa = round(
            $cuotaBase * ((float) $prestamo->min_defensa / 100),
            2
        );
        $primerPeriodo = Carbon::create(
            (int) $pendientes->first()->gestion,
            (int) $pendientes->first()->mes,
            1
        );
        $cronograma = [];

        for ($indice = 0; $indice < $numeroCuotas; $indice++) {
            $cuotaOriginal = $pendientes->get($indice);
            $interes = round($saldo * $tasaMensual, 2);
            $capital = round($cuotaBase - $interes, 2);

            if ($indice === $numeroCuotas - 1 || $capital > $saldo) {
                $capital = $saldo;
            }

            $saldo = max(0, round($saldo - $capital, 2));
            $fecha = $primerPeriodo->copy()->addMonths($indice);
            $itf = round((float) ($cuotaOriginal?->itf ?? 0), 2);
            $papel = round((float) ($cuotaOriginal?->papel ?? 0), 2);
            $comision = round((float) ($cuotaOriginal?->comision_mindef ?? 0), 2);
            $reposicion = round((float) ($cuotaOriginal?->rep_formulario ?? 0), 2);

            $cronograma[] = [
                'nro_cuota' => $ultimaPagada + $indice + 1,
                'mes' => $fecha->month,
                'gestion' => $fecha->year,
                'cuota_fija' => round(
                    $capital + $interes + $minDefensa
                        + $itf + $papel + $comision + $reposicion,
                    2
                ),
                'amortizacion_cap' => round($capital, 2),
                'interes' => $interes,
                'min_defensa' => $minDefensa,
                'itf' => $itf,
                'papel' => $papel,
                'comision_mindef' => $comision,
                'rep_formulario' => $reposicion,
                'saldo' => $saldo,
            ];
        }

        return $cronograma;
    }
}
