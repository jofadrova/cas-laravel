<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios_mensuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->unique('envios_mensuales_lote_unique')
                ->constrained('lotes_mensuales')
                ->restrictOnDelete();
            $table->string('destinatario', 30)->default('MINDEF');
            $table->string('estado', 20)->default('BORRADOR');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('cerrado_por')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index('creado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios_mensuales');
    }
};
