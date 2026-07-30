<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lotes_mensuales')
            || ! Schema::hasTable('lote_prestamo_procesamientos')
            || ! Schema::hasTable('lote_archivos')) {
            return;
        }

        $lotes = DB::table('lotes_mensuales AS lm')
            ->join(
                'lote_prestamo_procesamientos AS pp',
                'pp.lote_mensual_id',
                '=',
                'lm.id'
            )
            ->where('lm.estado', 'PROCESADO')
            ->where(function ($query): void {
                $query
                    ->whereRaw(
                        '(SELECT COUNT(*) FROM lote_archivos AS au '
                        . "WHERE au.lote_mensual_id = lm.id AND au.tipo = 'UFV') < 3"
                    )
                    ->orWhereNotExists(function ($subconsulta): void {
                        $subconsulta
                            ->selectRaw('1')
                            ->from('lote_archivos AS ac')
                            ->whereColumn('ac.lote_mensual_id', 'lm.id')
                            ->where('ac.tipo', 'CERTIFICADOS');
                    });
            })
            ->orderByDesc('pp.id')
            ->get([
                'lm.id',
                'pp.estado_lote_anterior',
            ])
            ->unique('id');

        foreach ($lotes as $lote) {
            $estadoAnterior = (string) $lote->estado_lote_anterior;

            if ($estadoAnterior === '' || $estadoAnterior === 'PROCESADO') {
                $estadoAnterior = 'BORRADOR';
            }

            DB::table('lotes_mensuales')
                ->where('id', $lote->id)
                ->where('estado', 'PROCESADO')
                ->update([
                    'estado' => $estadoAnterior,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        /*
         * No se revierte: volver a PROCESADO recrearía el bloqueo incorrecto.
         * Los pagos de Préstamos conservan su propia trazabilidad y bloqueo.
         */
    }
};
