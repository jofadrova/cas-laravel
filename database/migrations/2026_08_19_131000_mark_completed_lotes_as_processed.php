<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lotes_mensuales AS lm')
            ->whereNotIn('lm.estado', ['PROCESADO', 'CERRADO', 'ANULADO'])
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('lote_prestamo_procesamientos AS pp')
                    ->whereColumn('pp.lote_mensual_id', 'lm.id')
                    ->where('pp.cantidad_pagos', '>', 0)
                    ->where('pp.estado_contable', 'PENDIENTE');
            })
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('lote_fvs_procesamientos AS fp')
                    ->whereColumn('fp.lote_mensual_id', 'lm.id')
                    ->where('fp.estado_contable', 'PENDIENTE');
            })
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('lote_certificado_aporte_procesamientos AS cp')
                    ->whereColumn('cp.lote_mensual_id', 'lm.id')
                    ->where('cp.estado_contable', 'PENDIENTE');
            })
            ->update([
                'estado' => 'PROCESADO',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No se revierte: los tres procesamientos conservan su trazabilidad.
    }
};
