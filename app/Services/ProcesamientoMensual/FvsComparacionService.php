<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteFvsRegistro;
use App\Models\LoteMensual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class FvsComparacionService
{
    public function ejecutar(LoteMensual $lote, ?int $usuarioId): array
    {
        if (DB::table('lote_fvs_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists()) {
            throw new LogicException(
                'FVS ya fue finalizado y está pendiente para Contabilidad.'
            );
        }

        $registros = LoteFvsRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('id')
            ->get(['id', 'codigo_personal']);

        if ($registros->isEmpty()) {
            throw new LogicException(
                'El lote no contiene registros FVS consolidados para comparar.'
            );
        }

        $instituciones = $this->cargarInstituciones($registros);
        $ahora = now();
        $resultados = [];
        $validos = 0;
        $noEncontrados = 0;

        foreach ($registros as $registro) {
            $codigo = $this->normalizarCodigoPersonal(
                $registro->codigo_personal
            );
            $institucion = $this->resolverInstitucion(
                $instituciones->get($codigo, collect())
            );

            if ($codigo !== '' && $institucion !== null) {
                $estado = LoteFvsRegistro::ESTADO_VALIDO;
                $observacion = 'CODIGO_PERSONAL encontrado en socio_institucion.papeleta.';
                $validos++;
            } else {
                $estado = LoteFvsRegistro::ESTADO_NO_ENCONTRADO;
                $observacion = $codigo === ''
                    ? 'El registro no contiene CODIGO_PERSONAL.'
                    : 'CODIGO_PERSONAL no fue encontrado en socio_institucion.papeleta.';
                $noEncontrados++;
            }

            $resultados[] = [
                'id' => $registro->id,
                'socio_institucion_id' => $institucion?->id,
                'id_socio' => $institucion?->id_socio,
                'estado' => $estado,
                'observacion' => $observacion,
                'comparado_por' => $usuarioId,
                'fecha_comparacion' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        DB::transaction(function () use ($lote, $resultados): void {
            LoteMensual::query()
                ->whereKey($lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (DB::table('lote_fvs_procesamientos')
                ->where('lote_mensual_id', $lote->id)
                ->exists()) {
                throw new LogicException(
                    'FVS ya fue finalizado y está pendiente para Contabilidad.'
                );
            }

            foreach ($resultados as $resultado) {
                $id = $resultado['id'];
                unset($resultado['id']);

                DB::table('lote_ufv_registros')
                    ->where('id', $id)
                    ->update($resultado);
            }
        });

        return [
            'total' => count($resultados),
            'validos' => $validos,
            'no_encontrados' => $noEncontrados,
        ];
    }

    private function cargarInstituciones(Collection $registros): Collection
    {
        $papeletas = $registros
            ->pluck('codigo_personal')
            ->map(fn ($codigo): string => $this->normalizarCodigoPersonal($codigo))
            ->filter()
            ->unique()
            ->values();

        if ($papeletas->isEmpty()) {
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
            ->whereIn(DB::raw($expresion), $papeletas->all())
            ->get([
                'si.id',
                'si.id_socio',
                'si.papeleta',
                'si.estado',
                's.estado AS estado_socio',
            ])
            ->groupBy(
                fn (object $institucion): string => $this->normalizarCodigoPersonal(
                    $institucion->papeleta
                )
            );
    }

    private function resolverInstitucion(Collection $instituciones): ?object
    {
        if ($instituciones->isEmpty()) {
            return null;
        }

        return $instituciones
            ->sortByDesc(
                fn (object $institucion): string =>
                    (strtoupper(trim((string) $institucion->estado)) === 'AC' ? '1' : '0')
                    .(strtoupper(trim((string) $institucion->estado_socio)) === 'AC' ? '1' : '0')
                    .str_pad((string) $institucion->id, 20, '0', STR_PAD_LEFT)
            )
            ->first();
    }

    private function normalizarCodigoPersonal(mixed $valor): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor);
            $valor = ltrim((string) $valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return preg_replace(
            '/\s+/',
            ' ',
            Str::upper(Str::ascii($valor))
        ) ?? '';
    }
}
