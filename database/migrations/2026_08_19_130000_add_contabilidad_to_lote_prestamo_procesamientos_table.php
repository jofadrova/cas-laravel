<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_prestamo_procesamientos', function (Blueprint $table) {
            $table->string('estado_contable', 30)
                ->default('PENDIENTE')
                ->after('monto_total');
            $table->unsignedBigInteger('asiento_contable_id')
                ->nullable()
                ->after('estado_contable');
            $table->index('estado_contable', 'lote_prestamo_proc_estado_contable_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lote_prestamo_procesamientos', function (Blueprint $table) {
            $table->dropIndex('lote_prestamo_proc_estado_contable_idx');
            $table->dropColumn(['estado_contable', 'asiento_contable_id']);
        });
    }
};
