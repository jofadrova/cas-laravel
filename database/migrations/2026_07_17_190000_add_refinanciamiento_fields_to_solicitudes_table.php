<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->boolean('refinanciado')->default(false);
            $table->unsignedBigInteger('id_solicitud_origen')->nullable()->index();
            $table->decimal('saldo_refinanciado', 13, 2)->nullable();
            $table->decimal('monto_desembolso_refinanciamiento', 13, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropIndex(['id_solicitud_origen']);
            $table->dropColumn([
                'refinanciado',
                'id_solicitud_origen',
                'saldo_refinanciado',
                'monto_desembolso_refinanciamiento',
            ]);
        });
    }
};
