<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reprogramaciones_prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud')->index();
            $table->unsignedInteger('cuotas_pagadas');
            $table->unsignedInteger('cuotas_pendientes_anterior');
            $table->unsignedInteger('cuotas_pendientes_nuevo');
            $table->unsignedInteger('periodo_anterior');
            $table->unsignedInteger('periodo_nuevo');
            $table->decimal('saldo_capital', 13, 2);
            $table->decimal('cuota_anterior', 13, 2);
            $table->decimal('cuota_nueva', 13, 2);
            $table->text('autorizacion');
            $table->text('observaciones')->nullable();
            $table->date('fecha');
            $table->char('estado', 2)->default('AC');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reprogramaciones_prestamos');
    }
};
