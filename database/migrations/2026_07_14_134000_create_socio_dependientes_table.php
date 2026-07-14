<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('socio_dependientes', function (Blueprint $table) {

            $table->id();

            $table->unsignedInteger('id_socio');

            $table->string('nombres', 35);
            $table->string('paterno', 35)->nullable();
            $table->string('materno', 35)->nullable();

            $table->string('ci', 15);
            $table->string('exp', 2);

            $table->string('parentesco', 2);

            $table->decimal('porcentaje', 5, 2);

            $table->string('estado', 2)->default('AC');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socio_dependientes');
    }
};
