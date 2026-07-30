<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_certificado_aporte_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->constrained('lotes_mensuales')
                ->cascadeOnDelete();
            $table->foreignId('lote_archivo_id')
                ->constrained('lote_archivos')
                ->cascadeOnDelete();
            $table->unsignedInteger('fila_origen');

            $table->unsignedSmallInteger('gestion');
            $table->string('mes', 20);
            $table->string('documento_respaldo', 50)->nullable();
            $table->string('eit_codorg', 30)->nullable();
            $table->string('organismos', 150)->nullable();
            $table->string('eit_codrep', 30)->nullable();
            $table->string('reparticion', 150)->nullable();
            $table->string('grupo', 30)->nullable();
            $table->string('descripcion_grupo', 150)->nullable();
            $table->string('identificador_acreedor', 50)->nullable();
            $table->string('acreedor', 150)->nullable();
            $table->string('codigo_concepto', 50)->nullable();
            $table->string('codigo_acreedor', 50)->nullable();
            $table->string('cta_bancaria_acreedor', 50)->nullable();
            $table->string('codigo_personal', 50)->nullable();
            $table->string('eit_item', 50)->nullable();
            $table->string('carnet', 50)->nullable();
            $table->string('grado', 80)->nullable();
            $table->string('mension', 100)->nullable();
            $table->string('nombres', 200)->nullable();
            $table->decimal('monto_descuento', 15, 6);
            $table->decimal('tot_2', 15, 6);
            $table->decimal('comision', 15, 6);
            $table->string('estado', 20)->default('IMPORTADO');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(
                ['lote_archivo_id', 'fila_origen'],
                'lote_cert_aporte_archivo_fila_unique'
            );
            $table->index(
                ['lote_mensual_id', 'estado'],
                'lote_cert_aporte_lote_estado_index'
            );
            $table->index('codigo_personal');
            $table->index('carnet');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_certificado_aporte_registros');
    }
};
