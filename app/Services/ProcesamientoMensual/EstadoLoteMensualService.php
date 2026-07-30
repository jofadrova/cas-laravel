<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;

class EstadoLoteMensualService
{
    // Valor físico existente en lote_archivos.tipo; funcionalmente es FVS.
    private const TIPO_FVS = 'UFV';
    private const TIPO_CERTIFICADOS = 'CERTIFICADOS';
    private const MINIMO_ARCHIVOS_FVS = 3;
    private const MINIMO_ARCHIVOS_CERTIFICADOS = 3;

    /**
     * PROCESADO pertenece al lote completo, no a uno de sus grupos.
     */
    public function sincronizar(LoteMensual $lote): bool
    {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return false;
        }

        $prestamosCompletos = DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists();

        $fvsCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_FVS)
            ->count() >= self::MINIMO_ARCHIVOS_FVS;

        $certificadosCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_CERTIFICADOS)
            ->count() >= self::MINIMO_ARCHIVOS_CERTIFICADOS;

        if (! $prestamosCompletos
            || ! $fvsCompletos
            || ! $certificadosCompletos) {
            return false;
        }

        if ($lote->estado !== LoteMensual::ESTADO_PROCESADO) {
            $lote->forceFill([
                'estado' => LoteMensual::ESTADO_PROCESADO,
            ])->save();
        }

        return true;
    }
}
