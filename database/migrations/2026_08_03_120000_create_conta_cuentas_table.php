<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_cuentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_padre_id')->nullable()->constrained('conta_cuentas')->restrictOnDelete();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 180);
            $table->string('tipo', 20);
            $table->char('naturaleza', 1);
            $table->char('moneda', 1)->default('B');
            $table->unsignedTinyInteger('nivel');
            $table->boolean('acepta_movimientos')->default(false);
            $table->boolean('estado')->default(true);
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->string('referencia_normativa', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['cuenta_padre_id', 'estado']);
            $table->index(['tipo', 'naturaleza']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_cuentas');
    }
};
