<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->unsignedTinyInteger('mes')->nullable()->after('id');
            $table->unsignedSmallInteger('gestion')->nullable()->after('mes');
            $table->decimal('tipo_cambio', 10, 5)->nullable()->after('gestion');
            $table->date('fecha_envio')->nullable()->after('destinatario');
        });

        DB::table('envios_mensuales as em')
            ->join('lotes_mensuales as lm', 'lm.id', '=', 'em.lote_mensual_id')
            ->update([
                'em.mes' => DB::raw('lm.mes'),
                'em.gestion' => DB::raw('lm.gestion'),
                'em.tipo_cambio' => DB::raw('lm.tipo_cambio'),
            ]);

        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->unique(
                ['mes', 'gestion'],
                'envios_mensuales_mes_gestion_unique'
            );
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->foreignId('envio_mensual_id')
                ->nullable()
                ->unique('lotes_mensuales_envio_unique')
                ->after('id')
                ->constrained('envios_mensuales')
                ->restrictOnDelete();
        });

        DB::statement(
            'UPDATE lotes_mensuales lm '
            .'INNER JOIN envios_mensuales em ON em.lote_mensual_id = lm.id '
            .'SET lm.envio_mensual_id = em.id'
        );

        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->dropForeign(['lote_mensual_id']);
            $table->dropUnique('envios_mensuales_lote_unique');
            $table->dropColumn('lote_mensual_id');
        });
    }

    public function down(): void
    {
        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->foreignId('lote_mensual_id')
                ->nullable()
                ->after('id')
                ->constrained('lotes_mensuales')
                ->restrictOnDelete();
        });

        DB::statement(
            'UPDATE envios_mensuales em '
            .'INNER JOIN lotes_mensuales lm ON lm.envio_mensual_id = em.id '
            .'SET em.lote_mensual_id = lm.id'
        );

        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->unique('lote_mensual_id', 'envios_mensuales_lote_unique');
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->dropForeign(['envio_mensual_id']);
            $table->dropUnique('lotes_mensuales_envio_unique');
            $table->dropColumn('envio_mensual_id');
        });

        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->dropUnique('envios_mensuales_mes_gestion_unique');
            $table->dropColumn(['mes', 'gestion', 'tipo_cambio', 'fecha_envio']);
        });
    }
};
