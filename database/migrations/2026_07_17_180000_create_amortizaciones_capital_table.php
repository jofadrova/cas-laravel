<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amortizaciones_capital', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud')->index();
            $table->foreignId('id_pago')->constrained('pagos')->cascadeOnDelete();
            $table->string('tipo_recalculo', 5);
            $table->decimal('monto_efectivo', 13, 2);
            $table->decimal('monto_capital', 13, 2);
            $table->decimal('tipo_cambio', 10, 5)->nullable();
            $table->decimal('saldo_anterior', 13, 2);
            $table->decimal('saldo_nuevo', 13, 2);
            $table->decimal('cuota_anterior', 13, 2);
            $table->decimal('cuota_nueva', 13, 2);
            $table->unsignedInteger('periodo_anterior');
            $table->unsignedInteger('periodo_nuevo');
            $table->text('autorizacion');
            $table->text('observaciones')->nullable();
            $table->date('fecha');
            $table->char('estado', 2)->default('AC');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amortizaciones_capital');
    }
};
