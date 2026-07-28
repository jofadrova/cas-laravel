<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_mensuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('gestion');
            $table->date('fecha_recepcion')->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            $table->text('observaciones')->nullable();

            /*
             * Se mantienen como identificadores sin llave foránea hasta
             * confirmar la estructura y el motor de la tabla users.
             */
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('cerrado_por')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            $table->timestamps();

            $table->unique(
                ['mes', 'gestion'],
                'lotes_mensuales_mes_gestion_unique'
            );
            $table->index('estado');
            $table->index('gestion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_mensuales');
    }
};