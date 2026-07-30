<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_prestamo_procesamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->unique()
                ->constrained('lotes_mensuales')
                ->restrictOnDelete();
            $table->string('estado_lote_anterior', 30);
            $table->unsignedInteger('cantidad_pagos')->default(0);
            $table->decimal('monto_total', 15, 2)->default(0);
            $table->unsignedBigInteger('procesado_por')->nullable();
            $table->timestamp('fecha_procesamiento');
            $table->timestamps();
        });

        Schema::create('lote_prestamo_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_prestamo_procesamiento_id')
                ->constrained('lote_prestamo_procesamientos')
                ->cascadeOnDelete();

            /*
             * No se crean llaves foráneas contra pagos, cuotas_solicitud
             * ni las demás tablas Legacy.
             */
            $table->unsignedBigInteger('lote_prestamo_conciliacion_id')
                ->nullable();
            $table->unsignedBigInteger('lote_prestamo_conciliacion_detalle_id')
                ->nullable();
            $table->unsignedBigInteger('lote_garante_registro_id')
                ->nullable();
            $table->unsignedBigInteger('id_cuota_solicitud')->unique();
            $table->unsignedBigInteger('pago_id')->unique();
            $table->string('concepto', 30);
            $table->decimal('monto', 15, 2);
            $table->timestamps();

            $table->index('lote_prestamo_conciliacion_id', 'lpp_conciliacion_idx');
            $table->index(
                'lote_prestamo_conciliacion_detalle_id',
                'lpp_conciliacion_detalle_idx'
            );
            $table->index('lote_garante_registro_id', 'lpp_garante_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_prestamo_pagos');
        Schema::dropIfExists('lote_prestamo_procesamientos');
    }
};
