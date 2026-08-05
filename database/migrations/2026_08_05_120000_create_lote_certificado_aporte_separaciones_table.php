<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_certificado_aporte_separaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->constrained('lotes_mensuales')
                ->cascadeOnDelete();
            $table->foreignId('lote_certificado_aporte_registro_id')
                ->unique('lote_cert_aporte_separacion_registro_unique')
                ->constrained(
                    'lote_certificado_aporte_registros',
                    'id',
                    'lote_cert_aporte_sep_registro_fk'
                )
                ->cascadeOnDelete();
            $table->decimal('monto_total', 15, 2);
            $table->decimal('monto_ao', 15, 2)->default(0);
            $table->decimal('monto_av', 15, 2)->default(0);
            $table->decimal('monto_ai', 15, 2)->default(0);
            $table->string('regla', 40)->default('BLOQUES_100');
            $table->unsignedBigInteger('separado_por')->nullable();
            $table->timestamp('fecha_separacion');
            $table->timestamps();

            $table->index('lote_mensual_id', 'lote_cert_aporte_separacion_lote_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_certificado_aporte_separaciones');
    }
};
