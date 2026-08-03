<?php

namespace Tests\Unit;

use App\Models\LoteMensual;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LoteMensualTest extends TestCase
{
    public function test_un_lote_borrador_puede_editarse(): void
    {
        $lote = new LoteMensual(['estado' => LoteMensual::ESTADO_BORRADOR]);

        $this->assertTrue($lote->puedeEditar());
    }

    #[DataProvider('estadosBloqueados')]
    public function test_un_lote_fuera_de_borrador_no_puede_editarse(string $estado): void
    {
        $lote = new LoteMensual(['estado' => $estado]);

        $this->assertFalse($lote->puedeEditar());
    }

    public static function estadosBloqueados(): array
    {
        return [
            'cargado' => [LoteMensual::ESTADO_CARGADO],
            'procesado' => [LoteMensual::ESTADO_PROCESADO],
            'cerrado' => [LoteMensual::ESTADO_CERRADO],
            'anulado' => [LoteMensual::ESTADO_ANULADO],
        ];
    }
}
