<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });
    }

    public function down(): void
    {
        Schema::table('envios_mensuales', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 10, 5)
                ->nullable()
                ->after('gestion');
        });

        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 10, 5)
                ->nullable()
                ->after('gestion');
        });
    }
};
