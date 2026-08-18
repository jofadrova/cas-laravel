<?php

namespace Tests\Unit;

use App\Services\ProcesamientoMensual\CertificadoAporteRegulacionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CertificadoAporteRegulacionServiceTest extends TestCase
{
    #[DataProvider('casosProvider')]
    public function test_calcula_la_tasa_y_el_total_descuento(
        float $monto,
        bool $esIncorporado,
        float $tasaEsperada,
        float $totalEsperado
    ): void {
        $resultado = (new CertificadoAporteRegulacionService())
            ->calcularMontos($monto, $esIncorporado);

        $this->assertSame($tasaEsperada, $resultado['tasa_regulacion']);
        $this->assertSame($totalEsperado, $resultado['total_descuento']);
    }

    public static function casosProvider(): array
    {
        return [
            'socio incorporado' => [177.70, true, 0.50, 177.20],
            'socio asociado' => [605.36, false, 0.0, 605.36],
        ];
    }
}
