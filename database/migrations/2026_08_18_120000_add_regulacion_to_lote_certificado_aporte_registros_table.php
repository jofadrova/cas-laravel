<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lote_certificado_aporte_registros', function (Blueprint $table) {
            $table->decimal('tasa_regulacion', 15, 6)
                ->default(0)
                ->after('comision');
            $table->decimal('total_descuento', 15, 6)
                ->default(0)
                ->after('tasa_regulacion');
        });

        $papeletaNormalizada = <<<'SQL'
COALESCE(
    NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(si.papeleta AS CHAR))), ''),
    '0'
)
SQL;
        $codigoNormalizado = <<<'SQL'
COALESCE(
    NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(r.codigo_personal AS CHAR))), ''),
    '0'
)
SQL;

        DB::statement(<<<SQL
UPDATE lote_certificado_aporte_registros AS r
SET r.tasa_regulacion = CASE WHEN EXISTS (
    SELECT 1
    FROM socio_institucion AS si
    INNER JOIN socios AS s ON s.id = si.id_socio
    WHERE {$papeletaNormalizada} = {$codigoNormalizado}
      AND s.estado = 'IN'
) THEN 0.50 ELSE 0 END
SQL);

        DB::statement(<<<'SQL'
UPDATE lote_certificado_aporte_registros
SET total_descuento = monto_descuento - tasa_regulacion
SQL);
    }

    public function down(): void
    {
        Schema::table('lote_certificado_aporte_registros', function (Blueprint $table) {
            $table->dropColumn(['tasa_regulacion', 'total_descuento']);
        });
    }
};
