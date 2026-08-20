<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envio_mensual_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_mensual_id')
                ->constrained('envios_mensuales')
                ->restrictOnDelete();
            $table->string('tipo', 30);
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('mime_type', 100)->default('text/plain');
            $table->char('hash_sha256', 64);
            $table->unsignedInteger('cantidad_registros');
            $table->decimal('monto_total', 15, 2);
            $table->unsignedBigInteger('generado_por')->nullable();
            $table->timestamp('generado_en');
            $table->timestamps();

            $table->unique(
                ['envio_mensual_id', 'tipo'],
                'envio_mensual_archivos_envio_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_mensual_archivos');
    }
};
