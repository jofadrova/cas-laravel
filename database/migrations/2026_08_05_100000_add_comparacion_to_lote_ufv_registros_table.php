<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_ufv_registros', function (Blueprint $table) {
            $table->unsignedBigInteger('socio_institucion_id')
                ->nullable()
                ->after('observacion')
                ->index();
            $table->unsignedBigInteger('id_socio')
                ->nullable()
                ->after('socio_institucion_id')
                ->index();
            $table->unsignedBigInteger('comparado_por')
                ->nullable()
                ->after('id_socio');
            $table->timestamp('fecha_comparacion')
                ->nullable()
                ->after('comparado_por');
        });
    }

    public function down(): void
    {
        Schema::table('lote_ufv_registros', function (Blueprint $table) {
            $table->dropIndex(['socio_institucion_id']);
            $table->dropIndex(['id_socio']);
            $table->dropColumn([
                'socio_institucion_id',
                'id_socio',
                'comparado_por',
                'fecha_comparacion',
            ]);
        });
    }
};
