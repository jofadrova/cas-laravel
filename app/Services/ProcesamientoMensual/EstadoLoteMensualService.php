<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;

class EstadoLoteMensualService
{
    public const ESTADO_CONTABLE_PENDIENTE = 'PENDIENTE';

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
     * PROCESADO se asigna cuando Préstamos tiene pagos y los tres grupos
     * quedaron pendientes para Contabilidad, sin importar el orden de cierre.
     */
    public function sincronizar(LoteMensual $lote): bool
    {
        if ($lote->estado === LoteMensual::ESTADO_PROCESADO) {
            return true;
        }

        if (in_array($lote->estado, [
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return false;
        }

        if ($this->procesamientoCompleto($lote->id)) {
            $lote->forceFill([
                'estado' => LoteMensual::ESTADO_PROCESADO,
            ])->save();

            return true;
        }

        $prestamosCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_PRESTAMOS)
            ->count() >= self::MINIMO_ARCHIVOS_PRESTAMOS;

        $fvsCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_FVS)
            ->where('ruta', 'not like', '%/otros/%')
            ->count() >= self::MINIMO_ARCHIVOS_FVS;

        $certificadosCompletos = DB::table('lote_archivos')
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', self::TIPO_CERTIFICADOS)
            ->where('ruta', 'not like', '%/otros/%')
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

    public function procesamientoCompleto(int $loteId): bool
    {
        $prestamosPendientes = DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $loteId)
            ->where('cantidad_pagos', '>', 0)
            ->where('estado_contable', self::ESTADO_CONTABLE_PENDIENTE)
            ->exists();
        $fvsPendiente = DB::table('lote_fvs_procesamientos')
            ->where('lote_mensual_id', $loteId)
            ->where('estado_contable', self::ESTADO_CONTABLE_PENDIENTE)
            ->exists();
        $certificadosPendientes = DB::table('lote_certificado_aporte_procesamientos')
            ->where('lote_mensual_id', $loteId)
            ->where('estado_contable', self::ESTADO_CONTABLE_PENDIENTE)
            ->exists();

        return $prestamosPendientes && $fvsPendiente && $certificadosPendientes;
    }
}
