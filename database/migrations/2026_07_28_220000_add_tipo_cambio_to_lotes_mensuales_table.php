<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_mensuales', function (Blueprint $table) {
            /*
             * Nullable únicamente para permitir la migración de los lotes
             * creados antes de incorporar el tipo de cambio. Los formularios
             * de creación y edición lo exigen obligatoriamente.
             */
            $table->decimal('tipo_cambio', 10, 5)
                ->nullable()
                ->after('gestion');
        });
    }

    public function down(): void
    {
        Schema::table('lotes_mensuales', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });
    }
};
