<?php

namespace App\Services\ProcesamientoMensual;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CertificadoAporteRegulacionService
{
    private const TASA_INCORPORADO = 0.50;

    public function aplicar(array $registros): array
    {
        $papeletas = collect($registros)
            ->pluck('codigo_personal')
            ->map(fn ($codigo): string => $this->normalizarCodigo($codigo))
            ->filter()
            ->unique()
            ->values();

        $incorporados = $this->cargarIncorporados($papeletas->all());

        return array_map(function (array $registro) use ($incorporados): array {
            $codigo = $this->normalizarCodigo($registro['codigo_personal'] ?? null);
            $montos = $this->calcularMontos(
                (float) $registro['monto_descuento'],
                $incorporados->has($codigo)
            );

            return [
                ...$registro,
                'tasa_regulacion' => $montos['tasa_regulacion'],
                'total_descuento' => $montos['total_descuento'],
            ];
        }, $registros);
    }

    public function calcularMontos(float $montoDescuento, bool $esIncorporado): array
    {
        $tasa = $esIncorporado ? self::TASA_INCORPORADO : 0.0;

        return [
            'tasa_regulacion' => $tasa,
            'total_descuento' => round($montoDescuento - $tasa, 6),
        ];
    }

    private function cargarIncorporados(array $papeletas): Collection
    {
        if ($papeletas === []) {
            return collect();
        }

        $expresion = <<<'SQL'
COALESCE(
    NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(si.papeleta AS CHAR))), ''),
    '0'
)
SQL;

        return DB::table('socio_institucion AS si')
            ->join('socios AS s', 's.id', '=', 'si.id_socio')
            ->where('s.estado', 'IN')
            ->whereIn(DB::raw($expresion), $papeletas)
            ->pluck('si.papeleta')
            ->mapWithKeys(fn ($papeleta): array => [
                $this->normalizarCodigo($papeleta) => true,
            ]);
    }

    private function normalizarCodigo(mixed $valor): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
            $valor = ltrim($valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return preg_replace('/\s+/', ' ', Str::upper(Str::ascii($valor))) ?? '';
    }
}
