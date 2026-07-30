<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_garante_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_mensual_id')
                ->constrained('lotes_mensuales')
                ->cascadeOnDelete();
            $table->foreignId('lote_archivo_id')
                ->constrained('lote_archivos')
                ->cascadeOnDelete();
            $table->unsignedInteger('fila_origen');
            $table->string('codigo_titular', 50);
            $table->string('nombre_titular', 200)->nullable();
            $table->string('tipo_garante', 20)->nullable();
            $table->string('codigo_garante', 50);
            $table->string('nombre_garante', 200)->nullable();
            $table->decimal('monto_bs', 15, 6);
            $table->text('observacion_excel')->nullable();

            // No se crean llaves foráneas contra tablas Legacy/MyISAM.
            $table->unsignedBigInteger('id_socio_titular')->nullable();
            $table->unsignedBigInteger('id_socio_garante')->nullable();
            $table->unsignedBigInteger('id_solicitud')->nullable();
            $table->unsignedBigInteger('id_cuota_solicitud')->nullable();
            $table->unsignedBigInteger('tipo_prestamo')->nullable();

            $table->decimal('factor_conversion', 10, 5)->default(6.96);
            $table->decimal('monto_aplicable', 15, 6)->default(0);
            $table->decimal('monto_acumulado', 15, 6)->default(0);
            $table->decimal('saldo_pendiente', 15, 6)->default(0);
            $table->string('estado_conciliacion', 30)->default('SIN_COMPARAR');
            $table->string('estado_aplicacion', 30)->default('IMPORTADO');
            $table->text('observacion_sistema')->nullable();
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->unsignedBigInteger('procesado_por')->nullable();
            $table->timestamp('fecha_procesamiento')->nullable();
            $table->timestamps();

            $table->unique(
                ['lote_archivo_id', 'fila_origen'],
                'lote_garante_archivo_fila_unique'
            );
            $table->index(
                ['lote_mensual_id', 'codigo_garante'],
                'lote_garante_lote_codigo_garante_index'
            );
            $table->index(
                ['codigo_titular', 'estado_aplicacion'],
                'lote_garante_titular_estado_index'
            );
            $table->index(
                ['id_cuota_solicitud', 'estado_aplicacion'],
                'lote_garante_cuota_estado_index'
            );
            $table->index('id_solicitud');
            $table->index('pago_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_garante_registros');
    }
};
