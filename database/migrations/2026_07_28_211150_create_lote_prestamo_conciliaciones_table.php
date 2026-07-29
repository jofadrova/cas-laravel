<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_prestamo_conciliaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->constrained('lotes_mensuales')
                ->cascadeOnDelete();
            $table->foreignId('lote_prestamo_registro_id')
                ->constrained('lote_prestamo_registros')
                ->cascadeOnDelete();
            $table->string('eit_item', 50)->nullable();
            $table->unsignedBigInteger('socio_institucion_id')->nullable();
            $table->unsignedBigInteger('id_socio')->nullable();
            $table->decimal('monto_excel', 15, 2)->default(0);
            $table->decimal('monto_base_datos', 15, 2)->default(0);
            $table->decimal('diferencia', 15, 2)->default(0);
            $table->string('clasificacion', 30);
            $table->unsignedInteger('cantidad_cuotas')->default(0);
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('conciliado_por')->nullable();
            $table->timestamps();

            $table->unique(
                'lote_prestamo_registro_id',
                'lote_prestamo_conciliacion_registro_unique'
            );
            $table->index(
                ['lote_mensual_id', 'clasificacion'],
                'lote_prestamo_conciliacion_lote_clasificacion_index'
            );
            $table->index('eit_item');
            $table->index('id_socio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_prestamo_conciliaciones');
    }
};