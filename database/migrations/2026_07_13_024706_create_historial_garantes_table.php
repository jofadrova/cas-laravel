<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('historial_garantes', function (Blueprint $table) {

            $table->id();

            $table->integer('id_solicitud');

            $table->foreign('id_solicitud')
                ->references('id_solicitud')
                ->on('solicitudes');
            $table->foreignId('id_usuario')->constrained('users');

            $table->integer('garante1_old')->nullable();
            $table->integer('garante2_old')->nullable();

            $table->integer('garante1_new')->nullable();
            $table->integer('garante2_new')->nullable();          

            $table->enum('tipo_cambio', [
                'GARANTE1',
                'GARANTE2',
                'AMBOS'
            ]);

            $table->dateTime('fecha');

            $table->text('observaciones')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_garantes');
    }
};
