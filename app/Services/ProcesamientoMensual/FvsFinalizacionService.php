<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteFvsRegistro;
use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;
use LogicException;

class FvsFinalizacionService
{
    public const ESTADO_CONTABLE_PENDIENTE = 'PENDIENTE';

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): object
    {
        return DB::transaction(function () use ($lote, $usuarioId): object {
            $loteBloqueado = LoteMensual::query()
                ->whereKey($lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($loteBloqueado->estado, [
                LoteMensual::ESTADO_PROCESADO,
                LoteMensual::ESTADO_CERRADO,
                LoteMensual::ESTADO_ANULADO,
            ], true)) {
                throw new LogicException(
                    "El lote se encuentra {$loteBloqueado->estado} y no admite esta operación."
                );
            }

            if (DB::table('lote_fvs_procesamientos')
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->exists()) {
                throw new LogicException(
                    'El procesamiento FVS ya fue finalizado y está pendiente para Contabilidad.'
                );
            }

            $base = LoteFvsRegistro::query()
                ->where('lote_mensual_id', $loteBloqueado->id);
            $total = (clone $base)->count();
            $validos = (clone $base)
                ->where('estado', LoteFvsRegistro::ESTADO_VALIDO)
                ->count();
            $observados = (clone $base)
                ->where('estado', LoteFvsRegistro::ESTADO_NO_ENCONTRADO)
                ->count();

            if ($total === 0) {
                throw new LogicException('No existen registros FVS para finalizar.');
            }

            if ($total !== $validos + $observados) {
                throw new LogicException(
                    'La comparación FVS está incompleta. Vuelva a comparar antes de finalizar.'
                );
            }

            $montoTotal = round(
                (float) (clone $base)->sum('monto_descuento'),
                2,
                PHP_ROUND_HALF_UP
            );
            $ahora = now();
            $id = DB::table('lote_fvs_procesamientos')->insertGetId([
                'lote_mensual_id' => $loteBloqueado->id,
                'cantidad_registros' => $total,
                'cantidad_validos' => $validos,
                'cantidad_observados' => $observados,
                'monto_total' => $montoTotal,
                'estado_contable' => self::ESTADO_CONTABLE_PENDIENTE,
                'asiento_contable_id' => null,
                'finalizado_por' => $usuarioId,
                'fecha_finalizacion' => $ahora,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            return DB::table('lote_fvs_procesamientos')->find($id);
        });
    }
}
