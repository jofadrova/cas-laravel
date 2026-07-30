<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->string('concepto', 30)
                ->default('CUOTA_PRESTAMO')
                ->after('orden_operacion');
            $table->foreignId('lote_garante_registro_id')
                ->nullable()
                ->after('concepto')
                ->constrained('lote_garante_registros')
                ->nullOnDelete();

            $table->index(
                ['lote_mensual_id', 'concepto'],
                'lp_conciliacion_lote_concepto_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->dropIndex('lp_conciliacion_lote_concepto_index');
            $table->dropConstrainedForeignId('lote_garante_registro_id');
            $table->dropColumn('concepto');
        });
    }
};
