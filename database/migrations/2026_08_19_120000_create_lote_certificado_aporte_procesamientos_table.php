<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_certificado_aporte_procesamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->unique('lote_cert_aporte_proc_lote_unique')
                ->constrained('lotes_mensuales', 'id', 'lote_cert_aporte_proc_lote_fk')
                ->restrictOnDelete();
            $table->unsignedInteger('cantidad_registros');
            $table->decimal('monto_descuento', 15, 2);
            $table->decimal('tasa_regulacion', 15, 2);
            $table->decimal('total_descuento', 15, 2);
            $table->decimal('monto_ao', 15, 2);
            $table->decimal('monto_av', 15, 2);
            $table->decimal('monto_ai', 15, 2);
            $table->string('estado_contable', 30)->default('PENDIENTE');
            $table->unsignedBigInteger('asiento_contable_id')->nullable();
            $table->unsignedBigInteger('consolidado_por')->nullable();
            $table->timestamp('fecha_consolidacion');
            $table->timestamps();

            $table->index('estado_contable', 'lote_cert_aporte_proc_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_certificado_aporte_procesamientos');
    }
};
