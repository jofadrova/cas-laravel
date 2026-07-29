<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->unsignedInteger('orden_operacion')
                ->default(1)
                ->after('lote_prestamo_registro_id');
            $table->decimal('monto_excel_asignado', 15, 2)
                ->default(0)
                ->after('monto_excel');
        });

        // La nueva clave compuesta debe existir antes de eliminar la anterior,
        // porque su primera columna mantiene indexada la llave foránea.
        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->unique(
                ['lote_prestamo_registro_id', 'orden_operacion'],
                'lp_conciliacion_registro_orden_unique'
            );
        });

        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->dropUnique(
                'lote_prestamo_conciliacion_registro_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->unique(
                'lote_prestamo_registro_id',
                'lote_prestamo_conciliacion_registro_unique'
            );
        });

        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->dropUnique(
                'lp_conciliacion_registro_orden_unique'
            );
        });

        Schema::table('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->dropColumn('monto_excel_asignado');
            $table->dropColumn('orden_operacion');
        });
    }
};