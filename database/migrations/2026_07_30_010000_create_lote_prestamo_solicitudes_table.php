<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'lote_prestamo_solicitudes',
            function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lote_prestamo_procesamiento_id')
                    ->constrained('lote_prestamo_procesamientos')
                    ->cascadeOnDelete();

                /*
                 * solicitudes es una tabla Legacy/MyISAM. No se crea una
                 * llave foránea mientras se completa la modernización.
                 */
                $table->unsignedBigInteger('id_solicitud');
                $table->unsignedInteger('ultima_cuota_anterior')
                    ->default(0);
                $table->decimal('saldo_actual_anterior', 15, 2)
                    ->default(0);
                $table->string('estado_anterior', 2);
                $table->unsignedInteger('ultima_cuota_nueva');
                $table->decimal('saldo_actual_nuevo', 15, 2);
                $table->string('estado_nuevo', 2);
                $table->timestamps();

                $table->unique(
                    [
                        'lote_prestamo_procesamiento_id',
                        'id_solicitud',
                    ],
                    'lps_procesamiento_solicitud_unique'
                );
                $table->index('id_solicitud', 'lps_solicitud_idx');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_prestamo_solicitudes');
    }
};
