
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_cuotas', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('id_cuota_solicitud');

            $table->unsignedBigInteger('id_pago');

            $table->integer('nro_cuota');

            $table->decimal('monto', 13, 2);

            $table->date('fecha');

            $table->char('estado', 2)->default('AC');

            $table->timestamps();

            $table->foreign('id_pago')
                  ->references('id')
                  ->on('pagos')
                  ->cascadeOnDelete();
/*
            $table->foreign('id_cuota_solicitud')
                  ->references('id')
                  ->on('cuotas_solicitud')
                  ->cascadeOnDelete();
*/
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_cuotas');
    }
};
