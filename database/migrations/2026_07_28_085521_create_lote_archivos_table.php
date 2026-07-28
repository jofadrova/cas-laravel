<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->constrained('lotes_mensuales')
                ->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('extension', 10);
            $table->string('mime_type', 150)->nullable();
            $table->string('hash_sha256', 64);
            $table->unsignedInteger('filas_importadas')->default(0);
            $table->decimal('total_monto_descuento', 15, 6)->default(0);
            $table->decimal('total_tot_2', 15, 6)->default(0);
            $table->decimal('total_comision', 15, 6)->default(0);
            $table->string('estado', 20)->default('CARGADO');
            $table->unsignedBigInteger('cargado_por')->nullable();
            $table->timestamps();

            $table->unique(
                ['lote_mensual_id', 'tipo', 'hash_sha256'],
                'lote_archivos_lote_tipo_hash_unique'
            );
            $table->index(['lote_mensual_id', 'tipo']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_archivos');
    }
};
