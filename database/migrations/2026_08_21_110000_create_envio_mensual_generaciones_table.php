<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        $envios = DB::table('envios_mensuales')->orderBy('id')->get(['id']);

        foreach ($envios as $envio) {
            $txt = DB::table('envio_mensual_archivos')
                ->where('envio_mensual_id', $envio->id)
                ->where('tipo', 'PRESTAMOS')
                ->first();
            $garantes = DB::table('envio_mensual_archivos')
                ->where('envio_mensual_id', $envio->id)
                ->where('tipo', 'GARANTES_ORIGEN')
                ->first();

            if (! $txt || ! $garantes) {
                continue;
            }

            $cantidadGarantes = (int) $garantes->cantidad_registros;
            $montoGarantes = (float) $garantes->monto_total;
            $generacionId = DB::table('envio_mensual_generaciones')->insertGetId([
                'envio_mensual_id' => $envio->id,
                'version' => 1,
                'txt_nombre' => $txt->nombre_original,
                'txt_ruta' => $txt->ruta,
                'txt_hash_sha256' => $txt->hash_sha256,
                'cantidad_prestamos' => max(
                    0,
                    (int) $txt->cantidad_registros - $cantidadGarantes
                ),
                'monto_prestamos' => max(
                    0,
                    (float) $txt->monto_total - $montoGarantes
                ),
                'cantidad_garantes' => $cantidadGarantes,
                'monto_garantes' => $montoGarantes,
                'cantidad_total' => (int) $txt->cantidad_registros,
                'monto_total' => $txt->monto_total,
                'garantes_nombre' => $garantes->nombre_original,
                'garantes_ruta' => $garantes->ruta,
                'garantes_hash_sha256' => $garantes->hash_sha256,
                'generado_por' => $txt->generado_por,
                'generado_en' => $txt->generado_en,
                'created_at' => $txt->created_at,
                'updated_at' => $txt->updated_at,
            ]);

            DB::table('lotes_mensuales')
                ->where('envio_mensual_id', $envio->id)
                ->whereNull('envio_mensual_generacion_id')
                ->update(['envio_mensual_generacion_id' => $generacionId]);
        }
    }

    public function down(): void
    {
        Schema::table('lote_archivos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('envio_mensual_generacion_id');
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('envio_mensual_generacion_id');
        });

        Schema::dropIfExists('envio_mensual_generaciones');
    }
};
