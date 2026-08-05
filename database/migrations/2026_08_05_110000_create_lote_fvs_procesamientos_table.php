<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_fvs_procesamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->unique()
                ->constrained('lotes_mensuales')
                ->restrictOnDelete();
            $table->unsignedInteger('cantidad_registros');
            $table->unsignedInteger('cantidad_validos');
            $table->unsignedInteger('cantidad_observados');
            $table->decimal('monto_total', 15, 2);
            $table->string('estado_contable', 30)->default('PENDIENTE');
            $table->unsignedBigInteger('asiento_contable_id')->nullable();
            $table->unsignedBigInteger('finalizado_por')->nullable();
            $table->timestamp('fecha_finalizacion');
            $table->timestamps();

            $table->index('estado_contable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_fvs_procesamientos');
    }
};
