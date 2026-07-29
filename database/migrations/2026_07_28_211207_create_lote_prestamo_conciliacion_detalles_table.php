<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_prestamo_conciliacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lote_prestamo_conciliacion_id');
            $table->foreign(
                'lote_prestamo_conciliacion_id',
                'lp_conc_det_conciliacion_fk'
            )
                ->references('id')
                ->on('lote_prestamo_conciliaciones')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('id_solicitud');
            $table->unsignedBigInteger('id_cuota_solicitud');
            $table->unsignedBigInteger('tipo_prestamo')->nullable();
            $table->string('descripcion_tipo', 150)->nullable();
            $table->string('grupo_comparacion', 30)->nullable();
            $table->unsignedInteger('nro_cuota')->nullable();
            $table->decimal('monto_cuota', 15, 2)->default(0);
            $table->timestamps();

            $table->index('id_solicitud');
            $table->index('id_cuota_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_prestamo_conciliacion_detalles');
    }
};