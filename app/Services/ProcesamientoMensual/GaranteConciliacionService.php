<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GaranteConciliacionService
{
    private const TIPO_PRESTAMO_REGULAR = 1;

    private const FACTOR_REGULAR = 6.96;

    private const TOLERANCIA_CENTAVOS = 1;

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): Collection
    {
        $registros = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->whereNotIn('estado_aplicacion', [
                LoteGaranteRegistro::APLICACION_APLICADO,
                LoteGaranteRegistro::APLICACION_ANULADO,
            ])
            ->orderBy('id')
            ->get();

        if ($registros->isEmpty()) {
            return collect();
        }

        $sociosPorCodigo = $this->cargarSociosPorCodigo(
            $registros
                ->pluck('codigo_titular')
                ->merge($registros->pluck('codigo_garante'))
        );

        DB::transaction(function () use (
            $registros,
            $sociosPorCodigo,
            $usuarioId
        ): void {
            foreach ($registros->groupBy('codigo_titular') as $grupo) {
                $this->resolverGrupo(
                    $grupo,
                    $sociosPorCodigo,
                    $usuarioId
                );
            }

        });

        return LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('id')
            ->get();
    }

    private function resolverGrupo(
        Collection $grupo,
        Collection $sociosPorCodigo,
        ?int $usuarioId
    ): void {
        /** @var LoteGaranteRegistro $primero */
        $primero = $grupo->first();
        $titular = $sociosPorCodigo->get(
            $this->normalizarCodigo($primero->codigo_titular)
        );

        if ($titular === null) {
            $this->observarGrupo(
                $grupo,
                'El CODIGO TITULAR no fue encontrado en socio_institucion.papeleta.',
                $usuarioId
            );

            return;
        }

        $garantes = collect();
        $codigosNoEncontrados = [];

        foreach ($grupo as $registro) {
            $codigo = $this->normalizarCodigo($registro->codigo_garante);
            $garante = $sociosPorCodigo->get($codigo);

            if ($garante === null) {
                $codigosNoEncontrados[] = $codigo;

                continue;
            }

            $garantes->put((int) $garante->id_socio, $garante);
        }

        if ($codigosNoEncontrados !== []) {
            $this->observarGrupo(
                $grupo,
                'No se encontraron las papeletas de garante: '
                .implode(', ', array_unique($codigosNoEncontrados))
                .'.',
                $usuarioId,
                (int) $titular->id_socio
            );

            return;
        }

        $solicitudes = DB::table('solicitudes AS s')
            ->where('s.ide_per', $titular->id_socio)
            ->where('s.tipo_prestamo', self::TIPO_PRESTAMO_REGULAR)
            ->whereIn('s.estado', ['AC', 'DI'])
            ->where(function ($consulta) use ($garantes): void {
                foreach ($garantes->keys() as $idSocioGarante) {
                    $consulta->where(function ($porGarante) use (
                        $idSocioGarante
                    ): void {
                        $porGarante
                            ->where(
                                's.id_garante1',
                                (int) $idSocioGarante
                            )
                            ->orWhere(
                                's.id_garante2',
                                (int) $idSocioGarante
                            );
                    });
                }
            })
            ->orderBy('s.id_solicitud')
            ->get([
                's.id_solicitud',
                's.ide_per',
                's.tipo_prestamo',
                's.id_garante1',
                's.id_garante2',
            ]);

        if ($solicitudes->isEmpty()) {
            $this->observarGrupo(
                $grupo,
                'No se encontró un préstamo REGULAR AC/DI del titular '
                .'que tenga registrados los garantes del archivo.',
                $usuarioId,
                (int) $titular->id_socio,
                $garantes
            );

            return;
        }

        if ($solicitudes->count() > 1) {
            $this->observarGrupo(
                $grupo,
                'Se encontraron varios préstamos REGULARES con los mismos '
                .'garantes. La solicitud debe seleccionarse manualmente.',
                $usuarioId,
                (int) $titular->id_socio,
                $garantes
            );

            return;
        }

        $solicitud = $solicitudes->first();
        $cuota = DB::table('cuotas_solicitud AS cs')
            ->where('cs.id_solicitud', $solicitud->id_solicitud)
            ->where('cs.estado', 'PE')
            ->orderBy('cs.gestion')
            ->orderBy('cs.mes')
            ->orderBy('cs.nro_cuota')
            ->orderBy('cs.id')
            ->first([
                'cs.id',
                'cs.nro_cuota',
                'cs.cuota_fija',
                'cs.mes',
                'cs.gestion',
            ]);

        if ($cuota === null) {
            $this->observarGrupo(
                $grupo,
                'El préstamo REGULAR identificado no tiene cuotas PE.',
                $usuarioId,
                (int) $titular->id_socio,
                $garantes,
                (int) $solicitud->id_solicitud
            );

            return;
        }

        foreach ($grupo as $registro) {
            $garante = $sociosPorCodigo->get(
                $this->normalizarCodigo($registro->codigo_garante)
            );

            $registro->forceFill([
                'id_socio_titular' => $titular->id_socio,
                'id_socio_garante' => $garante?->id_socio,
                'id_solicitud' => $solicitud->id_solicitud,
                'id_cuota_solicitud' => $cuota->id,
                'tipo_prestamo' => self::TIPO_PRESTAMO_REGULAR,
                'factor_conversion' => self::FACTOR_REGULAR,
                'monto_aplicable' => round(
                    ((float) $registro->monto_bs) / self::FACTOR_REGULAR,
                    6
                ),
                'estado_aplicacion' => LoteGaranteRegistro::APLICACION_PENDIENTE,
                'observacion_sistema' => 'Préstamo REGULAR y cuota PE más antigua identificados.',
                'procesado_por' => $usuarioId,
                'fecha_procesamiento' => now(),
            ])->save();
        }
    }

    public function actualizarMontosDesdeExcel(
        Collection $registros,
        array $montosExcelCentavos
    ): void {
        $cuotaIds = collect();

        foreach ($registros as $registro) {
            if ($registro->id_cuota_solicitud === null
                || $registro->estado_aplicacion
                    === LoteGaranteRegistro::APLICACION_ANULADO
                || $registro->estado_aplicacion
                    === LoteGaranteRegistro::APLICACION_APLICADO) {
                continue;
            }

            $montoExcelCentavos = (int) ($montosExcelCentavos[$registro->id] ?? 0);
            $factor = (float) ($registro->factor_conversion ?: self::FACTOR_REGULAR);

            if ($montoExcelCentavos > 0) {
                $registro->forceFill([
                    'monto_aplicable' => $this->desdeCentavos(
                        (int) round($montoExcelCentavos / $factor)
                    ),
                    'estado_aplicacion' => LoteGaranteRegistro::APLICACION_PENDIENTE,
                    'observacion_sistema' => 'Descuento del garante verificado en los archivos Excel principales.',
                    'updated_at' => now(),
                ])->save();

                $cuotaIds->push($registro->id_cuota_solicitud);

                continue;
            }

            $registro->forceFill([
                'monto_aplicable' => 0,
                'monto_acumulado' => 0,
                'saldo_pendiente' => 0,
                'estado_aplicacion' => LoteGaranteRegistro::APLICACION_OBSERVADO,
                'observacion_sistema' => 'La papeleta del garante no tiene un descuento asignable en los archivos Excel principales.',
                'updated_at' => now(),
            ])->save();

            $cuotaIds->push($registro->id_cuota_solicitud);
        }

        $this->actualizarAcumulados($cuotaIds->unique()->values());
    }

    private function actualizarAcumulados(Collection $cuotaIds): void
    {
        foreach ($cuotaIds as $cuotaId) {
            $cuota = DB::table('cuotas_solicitud')
                ->where('id', $cuotaId)
                ->first(['id', 'cuota_fija', 'estado']);

            if ($cuota === null || strtoupper((string) $cuota->estado) !== 'PE') {
                LoteGaranteRegistro::query()
                    ->where('id_cuota_solicitud', $cuotaId)
                    ->whereNotIn('estado_aplicacion', [
                        LoteGaranteRegistro::APLICACION_APLICADO,
                        LoteGaranteRegistro::APLICACION_ANULADO,
                    ])
                    ->update([
                        'estado_aplicacion' => LoteGaranteRegistro::APLICACION_OBSERVADO,
                        'observacion_sistema' => 'La cuota asociada ya no se encuentra pendiente.',
                        'updated_at' => now(),
                    ]);

                continue;
            }

            $pendientes = LoteGaranteRegistro::query()
                ->where('id_cuota_solicitud', $cuotaId)
                ->whereNotIn('estado_aplicacion', [
                    LoteGaranteRegistro::APLICACION_APLICADO,
                    LoteGaranteRegistro::APLICACION_ANULADO,
                    LoteGaranteRegistro::APLICACION_OBSERVADO,
                ])
                ->lockForUpdate()
                ->get();

            $acumuladoCentavos = $pendientes->sum(
                fn (LoteGaranteRegistro $registro): int => $this->aCentavos($registro->monto_aplicable)
            );
            $cuotaCentavos = $this->aCentavos($cuota->cuota_fija);
            $diferencia = $acumuladoCentavos - $cuotaCentavos;

            if (abs($diferencia) <= self::TOLERANCIA_CENTAVOS) {
                $estado = LoteGaranteRegistro::APLICACION_LISTO;
                $observacion =
                    'El acumulado completa la cuota. Está listo para aplicar.';
            } elseif ($diferencia < 0) {
                $estado = LoteGaranteRegistro::APLICACION_PENDIENTE;
                $observacion = 'CUOTA INCOMPLETA · PENDIENTE PARA EL PRÓXIMO MES. '
                    .'El acumulado verificado es '
                    .$this->desdeCentavos($acumuladoCentavos)
                    .' y falta '
                    .$this->desdeCentavos(-$diferencia)
                    .'. Se conservará para combinarlo con el siguiente descuento.';
            } else {
                $estado = LoteGaranteRegistro::APLICACION_OBSERVADO;
                $observacion =
                    'El descuento acumulado supera la cuota pendiente.';
            }

            LoteGaranteRegistro::query()
                ->whereIn('id', $pendientes->pluck('id'))
                ->update([
                    'monto_acumulado' => $this->desdeCentavos($acumuladoCentavos),
                    'saldo_pendiente' => $this->desdeCentavos(max(0, -$diferencia)),
                    'estado_aplicacion' => $estado,
                    'observacion_sistema' => $observacion,
                    'updated_at' => now(),
                ]);
        }
    }

    private function observarGrupo(
        Collection $grupo,
        string $observacion,
        ?int $usuarioId,
        ?int $idSocioTitular = null,
        ?Collection $garantes = null,
        ?int $idSolicitud = null
    ): void {
        foreach ($grupo as $registro) {
            $garante = $garantes?->first(
                fn (object $item): bool => $this->normalizarCodigo($item->papeleta)
                    === $this->normalizarCodigo($registro->codigo_garante)
            );

            $registro->forceFill([
                'id_socio_titular' => $idSocioTitular,
                'id_socio_garante' => $garante?->id_socio,
                'id_solicitud' => $idSolicitud,
                'id_cuota_solicitud' => null,
                'tipo_prestamo' => self::TIPO_PRESTAMO_REGULAR,
                'factor_conversion' => self::FACTOR_REGULAR,
                'monto_aplicable' => round(
                    ((float) $registro->monto_bs) / self::FACTOR_REGULAR,
                    6
                ),
                'estado_aplicacion' => LoteGaranteRegistro::APLICACION_OBSERVADO,
                'observacion_sistema' => $observacion,
                'procesado_por' => $usuarioId,
                'fecha_procesamiento' => now(),
            ])->save();
        }
    }

    private function cargarSociosPorCodigo(Collection $codigos): Collection
    {
        $codigos = $codigos
            ->map(fn ($codigo): string => $this->normalizarCodigo($codigo))
            ->filter()
            ->unique()
            ->values();

        if ($codigos->isEmpty()) {
            return collect();
        }

        $expresion = <<<'SQL'
COALESCE(
    NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(papeleta AS CHAR))), ''),
    '0'
)
SQL;

        return DB::table('socio_institucion')
            ->select(['id', 'id_socio', 'papeleta', 'estado'])
            ->whereIn(DB::raw($expresion), $codigos->all())
            ->orderByRaw("CASE WHEN estado = 'AC' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->groupBy(
                fn (object $item): string => $this->normalizarCodigo($item->papeleta)
            )
            ->map(fn (Collection $items): ?object => $items->first());
    }

    private function normalizarCodigo(mixed $valor): string
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

        return $valor;
    }

    private function aCentavos(mixed $monto): int
    {
        return (int) round(((float) $monto) * 100);
    }

    private function desdeCentavos(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}
