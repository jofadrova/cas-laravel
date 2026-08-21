<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('lote_archivos', 'envio_mensual_generacion_id')) {
            Schema::table('lote_archivos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('envio_mensual_generacion_id');
            });
        }

        if (Schema::hasColumn('lotes_mensuales', 'envio_mensual_generacion_id')) {
            Schema::table('lotes_mensuales', function (Blueprint $table) {
                $table->dropConstrainedForeignId('envio_mensual_generacion_id');
            });
        }

        Schema::dropIfExists('envio_mensual_generaciones');

        DB::table('envio_mensual_archivos')
            ->where('tipo', 'PRESTAMOS')
            ->where('nombre_original', 'like', '%_PRESTAMOS_FINAL.txt')
            ->update([
                'nombre_original' => DB::raw(
                    "REPLACE(nombre_original, '_PRESTAMOS_FINAL.txt', '_PRESTAMOS.txt')"
                ),
            ]);
    }

    public function down(): void
    {
        Schema::create('envio_mensual_generaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_mensual_id')
                ->constrained('envios_mensuales')
                ->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('txt_nombre');
            $table->string('txt_ruta');
            $table->char('txt_hash_sha256', 64);
            $table->unsignedInteger('cantidad_prestamos');
            $table->decimal('monto_prestamos', 15, 2);
            $table->unsignedInteger('cantidad_garantes');
            $table->decimal('monto_garantes', 15, 2);
            $table->unsignedInteger('cantidad_total');
            $table->decimal('monto_total', 15, 2);
            $table->string('garantes_nombre');
            $table->string('garantes_ruta');
            $table->char('garantes_hash_sha256', 64);
            $table->unsignedBigInteger('generado_por')->nullable();
            $table->timestamp('generado_en');
            $table->timestamps();
            $table->unique(
                ['envio_mensual_id', 'version'],
                'envio_generaciones_envio_version_unique'
            );
            $table->index('garantes_hash_sha256');
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->foreignId('envio_mensual_generacion_id')
                ->nullable()
                ->after('envio_mensual_id')
                ->constrained('envio_mensual_generaciones')
                ->restrictOnDelete();
        });

        Schema::table('lote_archivos', function (Blueprint $table) {
            $table->foreignId('envio_mensual_generacion_id')
                ->nullable()
                ->after('lote_mensual_id')
                ->constrained('envio_mensual_generaciones')
                ->restrictOnDelete();
        });
    }
};
