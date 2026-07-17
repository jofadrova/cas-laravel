<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {

            $table->id();

            $table->decimal('monto', 13, 2);

            $table->char('tipo_moneda', 1)
                  ->comment('B=Bolivianos, U=Dólares');

            $table->date('fecha');

            $table->char('tipo_pago', 2)
                  ->comment('PC=Pago Cuota, PT=Pago Total, AM=Amortización');

            $table->text('anexo')->nullable();

            $table->char('estado', 2)->default('AC');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
