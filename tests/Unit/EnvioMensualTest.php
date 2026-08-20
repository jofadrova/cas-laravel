<?php

namespace Tests\Unit;

use App\Models\EnvioMensual;
use App\Models\LoteMensual;
use PHPUnit\Framework\TestCase;

class EnvioMensualTest extends TestCase
{
    public function test_genera_codigo_a_partir_del_periodo_de_origen(): void
    {
        $lote = new LoteMensual(['mes' => 8, 'gestion' => 2026]);
        $envio = new EnvioMensual;
        $envio->setRelation('loteMensual', $lote);

        $this->assertSame('ENV-202608', $envio->codigo);
    }

    public function test_declara_borrador_como_estado_inicial(): void
    {
        $this->assertContains(EnvioMensual::ESTADO_BORRADOR, EnvioMensual::ESTADOS);
    }
}
