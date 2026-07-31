<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;

class EstadoLoteMensualService
{
    private const TIPO_PRESTAMOS = 'PRESTAMOS';
    // Valor físico existente en lote_archivos.tipo; funcionalmente es FVS.
    private const TIPO_FVS = 'UFV';
    private const TIPO_CERTIFICADOS = 'CERTIFICADOS';
    private const MINIMO_ARCHIVOS_PRESTAMOS = 1;
    private const MINIMO_ARCHIVOS_FVS = 3;
    private const MINIMO_ARCHIVOS_CERTIFICADOS = 3;

    /**
     * El estado global solo describe la carga de los tres grupos.
     *
     * BORRADOR: falta al menos un grupo.
     * CARGADO: los tres grupos alcanzaron su cantidad mínima.
     *
     * PROCESADO se reservará para cuando los tres grupos cuenten con su
     * procesamiento de negocio terminado. El pago mensual de Préstamos se
     * controla independientemente en lote_prestamo_procesamientos.
     */
    public function sincronizar(LoteMensual $lote): bool
    {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return false;
        }

        $prestamosCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_PRESTAMOS)
            ->count() >= self::MINIMO_ARCHIVOS_PRESTAMOS;

        $fvsCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_FVS)
            ->count() >= self::MINIMO_ARCHIVOS_FVS;

        $certificadosCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_CERTIFICADOS)
            ->count() >= self::MINIMO_ARCHIVOS_CERTIFICADOS;

        $cargaCompleta = $prestamosCompletos
            && $fvsCompletos
            && $certificadosCompletos;
        $nuevoEstado = $cargaCompleta
            ? LoteMensual::ESTADO_CARGADO
            : LoteMensual::ESTADO_BORRADOR;

        if ($lote->estado !== $nuevoEstado) {
            $lote->forceFill([
                'estado' => $nuevoEstado,
            ])->save();
        }

        return $cargaCompleta;
    }
}
