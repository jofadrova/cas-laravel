<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $estadosRecalculables = [
            'BORRADOR',
            'CARGADO',
            'VALIDANDO',
            'OBSERVADO',
            'VALIDADO',
            'PROCESADO',
        ];

        DB::table('lotes_mensuales')
            ->whereIn('estado', $estadosRecalculables)
            ->orderBy('id')
            ->chunkById(100, function ($lotes): void {
                foreach ($lotes as $lote) {
                    $prestamos = DB::table('lote_archivos')
                        ->where('lote_mensual_id', $lote->id)
                        ->where('tipo', 'PRESTAMOS')
                        ->count();

                    // Valor físico conservado temporalmente para FVS.
                    $fvs = DB::table('lote_archivos')
                        ->where('lote_mensual_id', $lote->id)
                        ->where('tipo', 'UFV')
                        ->count();

                    $certificados = DB::table('lote_archivos')
                        ->where('lote_mensual_id', $lote->id)
                        ->where('tipo', 'CERTIFICADOS')
                        ->count();

                    $estado = $prestamos >= 1
                        && $fvs >= 3
                        && $certificados >= 3
                            ? 'CARGADO'
                            : 'BORRADOR';

                    DB::table('lotes_mensuales')
                        ->where('id', $lote->id)
                        ->update([
                            'estado' => $estado,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // La normalización de datos no puede reconstruir estados históricos
        // que nunca tuvieron una función operativa diferenciada.
    }
};
