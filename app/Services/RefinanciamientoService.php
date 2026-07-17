<?php

namespace App\Services;

use App\Models\CuotaSolicitud;
use App\Models\Prestamo;
use App\Services\Prestamos\CalculadoraPrestamo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefinanciamientoService
{
    public function __construct(
        private readonly CalculadoraPrestamo $calculadora
    ) {
    }

    public function aplicar(Prestamo $prestamo, array $datos): Prestamo
    {
        return DB::transaction(function () use ($prestamo, $datos) {
            $anterior = Prestamo::query()
                ->with('tipo')
                ->whereKey($prestamo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($anterior->estado !== 'AC' || (int) $anterior->tipo_prestamo !== 1) {
                throw ValidationException::withMessages([
                    'nuevo_monto' => 'El préstamo no está habilitado para refinanciamiento.',
                ]);
            }

            $nuevoMonto = round((float) $datos['nuevo_monto'], 2);
            $saldoRefinanciado = round((float) $anterior->saldo_actual, 2);
            $desembolso = round($nuevoMonto - $saldoRefinanciado, 2);

            if ($desembolso <= 0) {
                throw ValidationException::withMessages([
                    'nuevo_monto' => 'El nuevo monto debe ser mayor al saldo actual.',
                ]);
            }

            if ($nuevoMonto > (float) $anterior->tipo->monto_max) {
                throw ValidationException::withMessages([
                    'nuevo_monto' => 'El monto supera el máximo permitido para el préstamo Regular.',
                ]);
            }

            if ((int) $datos['plazo'] > (int) $anterior->tipo->plazo_max) {
                throw ValidationException::withMessages([
                    'plazo' => 'El plazo supera el máximo permitido para el préstamo Regular.',
                ]);
            }

            $tipoCambio = round((float) $datos['tipo_cambio'], 5);

            if ($tipoCambio <= 0) {
                throw ValidationException::withMessages([
                    'tipo_cambio' => 'Debe ingresar un tipo de cambio válido.',
                ]);
            }

            $simulacion = $this->calculadora->simular([
                'monto' => $nuevoMonto,
                'porcentaje' => (float) $anterior->tipo->porcentaje,
                'plazo' => (int) $datos['plazo'],
                'fecha' => $datos['fechaPrestamo'],
                'tipo_moneda' => $anterior->tipo->tipo_moneda,
                'tipo' => (int) $anterior->tipo->id_tasa,
                'itf' => (float) $anterior->tipo->itf,
                'papeleria' => (float) $anterior->tipo->papeleria,
                'min_defensa' => (float) $anterior->tipo->min_defensa,
                'gadm' => 0,
                'periodo_gadm' => 0,
            ]);

            $nuevo = Prestamo::create([
                'nro_solicitud' => (Prestamo::max('nro_solicitud') ?? 0) + 1,
                'ide_per' => $anterior->ide_per,
                'tipo_prestamo' => $anterior->tipo_prestamo,
                'id_garante1' => $datos['id_garante1'],
                'id_garante2' => $datos['id_garante2'],
                'monto' => $nuevoMonto,
                'monto_mora' => 0,
                'periodo' => (int) $datos['plazo'],
                'saldo_actual' => $nuevoMonto,
                'ultima_cuota' => 0,
                'motivo' => $datos['motivo'],
                'asiento' => $datos['asiento'],
                'interes' => $anterior->tipo->porcentaje,
                'min_defensa' => $anterior->tipo->min_defensa,
                'itf' => $anterior->tipo->itf,
                'estado' => 'AC',
                'gadm' => 0,
                'periodo_gadm' => 0,
                'fecha_deposito' => $datos['fechaPrestamo'],
                'tipo_cambio' => $tipoCambio,
                'editable' => false,
                'refinanciado' => true,
                'id_solicitud_origen' => $anterior->getKey(),
                'saldo_refinanciado' => $saldoRefinanciado,
                'monto_desembolso_refinanciamiento' => $desembolso,
            ]);

            foreach ($simulacion['cronograma'] as $fila) {
                $fecha = Carbon::parse($fila['fecha']);

                CuotaSolicitud::create([
                    'id_solicitud' => $nuevo->getKey(),
                    'nro_cuota' => $fila['numero'],
                    'mes' => $fecha->month,
                    'gestion' => $fecha->year,
                    'cuota_fija' => round($fila['cuota'], 2),
                    'amortizacion_cap' => round($fila['capital'], 2),
                    'interes' => round($fila['interes'], 2),
                    'min_defensa' => round($fila['min_defensa'], 2),
                    'itf' => round($fila['itf'], 2),
                    'papel' => round($fila['reposicion'], 2),
                    'capital_amortizado' => round($fila['capital'], 2),
                    'saldo' => round($fila['saldo'], 2),
                    'estado' => 'PE',
                    'comision_mindef' => null,
                    'rep_formulario' => null,
                ]);
            }

            $anterior->update([
                'estado' => 'CE',
                'editable' => false,
            ]);

            return $nuevo;
        });
    }
}
