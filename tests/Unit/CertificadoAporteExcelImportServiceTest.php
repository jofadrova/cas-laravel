<?php

namespace Tests\Unit;

use App\Services\ProcesamientoMensual\CertificadoAporteExcelImportService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CertificadoAporteExcelImportServiceTest extends TestCase
{
    #[DataProvider('montosProvider')]
    public function test_calcula_el_aporte_descontando_la_tasa_de_regulacion(
        float $montoDescuento,
        float $tasaRegulacion,
        float $esperado
    ): void {
        $servicio = new CertificadoAporteExcelImportService();

        $this->assertSame(
            $esperado,
            $servicio->calcularMontoAporte($montoDescuento, $tasaRegulacion)
        );
    }

    public static function montosProvider(): array
    {
        return [
            'con tasa' => [605.36, 5.36, 600.0],
            'sin tasa' => [177.70, 0.0, 177.70],
            'con seis decimales' => [100.123456, 0.023456, 100.1],
        ];
    }
}
