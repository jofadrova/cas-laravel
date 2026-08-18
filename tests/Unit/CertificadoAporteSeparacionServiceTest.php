<?php

namespace Tests\Unit;

use App\Services\ProcesamientoMensual\CertificadoAporteSeparacionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CertificadoAporteSeparacionServiceTest extends TestCase
{
    #[DataProvider('montosProvider')]
    public function test_separa_los_aportes_en_bloques_de_cien(
        float $monto,
        int $ao,
        int $av,
        int $ai
    ): void {
        $resultado = (new CertificadoAporteSeparacionService())->separarMonto($monto);

        $this->assertSame($ao, $resultado['ao']);
        $this->assertSame($av, $resultado['av']);
        $this->assertSame($ai, $resultado['ai']);
        $this->assertSame(
            $resultado['total'],
            $resultado['ao'] + $resultado['av'] + $resultado['ai']
        );
    }

    public static function montosProvider(): array
    {
        return [
            'Socio A' => [177.70, 10000, 0, 7770],
            'Socio B' => [605.36, 10000, 50000, 536],
            'Total descuento con tasa' => [177.20, 10000, 0, 7720],
        ];
    }
}
