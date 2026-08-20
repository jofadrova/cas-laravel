<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteCertificadoAporteRegistro;
use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;
use LogicException;

class CertificadoAporteSeparacionService
{
    private const BLOQUE_CENTAVOS = 10000;
    private const REGLA = 'LEGACY_TOTAL_DESCUENTO';

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): array
    {
        if (DB::table('lote_certificado_aporte_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists()) {
            throw new LogicException(
                'Los Certificados de Aportes ya fueron consolidados y están pendientes para Contabilidad.'
            );
        }

        $registros = LoteCertificadoAporteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('id')
            ->get(['id', 'total_descuento']);

        if ($registros->isEmpty()) {
            throw new LogicException(
                'El lote no contiene Certificados de Aportes consolidados para separar.'
            );
        }

        $ahora = now();
        $filas = [];
        $totales = ['total' => 0, 'ao' => 0, 'av' => 0, 'ai' => 0];

        foreach ($registros as $registro) {
            $separacion = $this->separarMonto((float) $registro->total_descuento);
            foreach ($totales as $clave => $valor) {
                $totales[$clave] += $separacion[$clave];
            }

            $filas[] = [
                'lote_mensual_id' => $lote->id,
                'lote_certificado_aporte_registro_id' => $registro->id,
                'monto_total' => $this->desdeCentavos($separacion['total']),
                'monto_ao' => $this->desdeCentavos($separacion['ao']),
                'monto_av' => $this->desdeCentavos($separacion['av']),
                'monto_ai' => $this->desdeCentavos($separacion['ai']),
                'regla' => self::REGLA,
                'separado_por' => $usuarioId,
                'fecha_separacion' => $ahora,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        if ($totales['total'] !== $totales['ao'] + $totales['av'] + $totales['ai']) {
            throw new LogicException('La separación de aportes no conserva el monto total.');
        }

        DB::transaction(function () use ($lote, $filas): void {
            LoteMensual::query()
                ->whereKey($lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (DB::table('lote_certificado_aporte_procesamientos')
                ->where('lote_mensual_id', $lote->id)
                ->exists()) {
                throw new LogicException(
                    'Los Certificados de Aportes ya fueron consolidados y están pendientes para Contabilidad.'
                );
            }

            foreach (array_chunk($filas, 500) as $bloque) {
                DB::table('lote_certificado_aporte_separaciones')->upsert(
                    $bloque,
                    ['lote_certificado_aporte_registro_id'],
                    [
                        'monto_total',
                        'monto_ao',
                        'monto_av',
                        'monto_ai',
                        'regla',
                        'separado_por',
                        'fecha_separacion',
                        'updated_at',
                    ]
                );
            }
        });

        return [
            'registros' => count($filas),
            'monto_total' => $this->desdeCentavos($totales['total']),
            'monto_ao' => $this->desdeCentavos($totales['ao']),
            'monto_av' => $this->desdeCentavos($totales['av']),
            'monto_ai' => $this->desdeCentavos($totales['ai']),
        ];
    }

    public function separarMonto(float $monto): array
    {
        $total = (int) round($monto * 100, 0, PHP_ROUND_HALF_UP);

        if ($total < 0) {
            throw new LogicException('No es posible separar un aporte negativo.');
        }

        $ao = $total >= self::BLOQUE_CENTAVOS
            ? self::BLOQUE_CENTAVOS
            : 0;
        $restante = $total - $ao;
        $av = intdiv($restante, self::BLOQUE_CENTAVOS)
            * self::BLOQUE_CENTAVOS;
        $ai = $restante - $av;

        return compact('total', 'ao', 'av', 'ai');
    }

    private function desdeCentavos(int $monto): float
    {
        return $monto / 100;
    }
}
