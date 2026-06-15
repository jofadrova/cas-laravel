<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('username', 50)
                  ->unique()
                  ->after('id');

            $table->string('estado', 20)
                  ->default('ACTIVO')
                  ->after('password');

            $table->timestamp('ultimo_acceso')
                  ->nullable()
                  ->after('estado');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('ultimo_acceso');
            $table->dropColumn('estado');
            $table->dropColumn('username');

        });
    }
};
