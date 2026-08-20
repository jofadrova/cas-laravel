<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use App\Models\LotePrestamoConciliacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

class PagoMensualPrestamoService
{
    private const TOLERANCIA_CENTAVOS = 1;

    public function __construct(
        private readonly EstadoLoteMensualService $estadoLote
    ) {}

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): array
    {
        return DB::transaction(function () use ($lote, $usuarioId): array {
            /** @var LoteMensual|null $loteBloqueado */
            $loteBloqueado = LoteMensual::query()
                ->whereKey($lote->id)
                ->lockForUpdate()
                ->first();

            if ($loteBloqueado === null) {
                throw new LogicException('El lote mensual ya no existe.');
            }

            $yaProcesado = DB::table('lote_prestamo_procesamientos')
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->exists();

            if ($yaProcesado
                || in_array($loteBloqueado->estado, [
                    LoteMensual::ESTADO_PROCESADO,
                    LoteMensual::ESTADO_CERRADO,
                ], true)) {
                throw new LogicException(
                    'El pago mensual de este lote ya fue consolidado. '
                    .'No se generó ningún registro duplicado.'
                );
            }

            if ($loteBloqueado->estado === LoteMensual::ESTADO_ANULADO) {
                throw new LogicException(
                    'No es posible consolidar pagos de un lote anulado.'
                );
            }

            $this->validarConciliacionDisponible($loteBloqueado);

            $operacionesNoCoinciden = LotePrestamoConciliacion::query()
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->soloAplicables()
                ->where(function ($query): void {
                    $query
                        ->whereNull('clasificacion')
                        ->orWhere(
                            'clasificacion',
                            '<>',
                            LotePrestamoConciliacion::COINCIDE
                        );
                })
                ->count();

            $descuentosGarantesPendientes = LoteGaranteRegistro::query()
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->where(
                    'estado_conciliacion',
                    LoteGaranteRegistro::CONCILIACION_COINCIDE
                )
                ->where(
                    'estado_aplicacion',
                    LoteGaranteRegistro::APLICACION_PENDIENTE
                )
                ->count();

            $pagosNormales = $this->cargarPagosNormales($loteBloqueado);
            $pagosGarantes = $this->cargarPagosGarantes($loteBloqueado);
            $candidatos = $pagosNormales
                ->concat($pagosGarantes)
                ->values();

            if ($candidatos->isEmpty()) {
                throw new LogicException(
                    'No existen cuotas listas para consolidar en este lote.'
                );
            }

            $duplicadas = $candidatos
                ->groupBy('id_cuota_solicitud')
                ->filter(fn (Collection $grupo): bool => $grupo->count() > 1);

            if ($duplicadas->isNotEmpty()) {
                throw new LogicException(
                    'Una misma cuota fue identificada simultáneamente como '
                    .'pago normal y descuento a garantes. Revise las cuotas ID: '
                    .$duplicadas->keys()->implode(', ')
                    .'. No se creó ningún pago.'
                );
            }

            $cuotaIds = $candidatos
                ->pluck('id_cuota_solicitud')
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values();

            $cuotas = DB::table('cuotas_solicitud AS cs')
                ->join(
                    'solicitudes AS s',
                    's.id_solicitud',
                    '=',
                    'cs.id_solicitud'
                )
                ->leftJoin('tasa AS t', 't.id_tasa', '=', 's.tipo_prestamo')
                ->whereIn('cs.id', $cuotaIds)
                ->get([
                    'cs.id',
                    'cs.id_solicitud',
                    'cs.nro_cuota',
                    'cs.cuota_fija',
                    'cs.saldo',
                    'cs.estado',
                    's.tipo_prestamo',
                    's.periodo',
                    's.ultima_cuota AS ultima_cuota_solicitud',
                    's.saldo_actual',
                    's.estado AS estado_solicitud',
                    't.tipo_moneda',
                ])
                ->keyBy('id');

            $this->validarCuotas($candidatos, $cuotas);

            $pagosExistentes = DB::table('pagos_cuotas')
                ->whereIn('id_cuota_solicitud', $cuotaIds)
                ->where('estado', 'AC')
                ->pluck('id_cuota_solicitud')
                ->unique()
                ->values();

            if ($pagosExistentes->isNotEmpty()) {
                throw new LogicException(
                    'Las siguientes cuotas ya tienen un pago activo: '
                    .$pagosExistentes->implode(', ')
                    .'. El lote no fue procesado.'
                );
            }

            $ahora = now();
            $procesamientoId = DB::table('lote_prestamo_procesamientos')
                ->insertGetId([
                    'lote_mensual_id' => $loteBloqueado->id,
                    'estado_lote_anterior' => $loteBloqueado->estado,
                    'cantidad_pagos' => 0,
                    'monto_total' => 0,
                    'estado_contable' => EstadoLoteMensualService::ESTADO_CONTABLE_PENDIENTE,
                    'asiento_contable_id' => null,
                    'procesado_por' => $usuarioId,
                    'fecha_procesamiento' => $ahora,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);

            $cantidadPagos = 0;
            $montoTotalCentavos = 0;

            foreach ($candidatos as $candidato) {
                $cuota = $cuotas->get((int) $candidato['id_cuota_solicitud']);
                $monto = $this->desdeCentavos(
                    $this->aCentavos($cuota->cuota_fija)
                );
                $anexo = $candidato['concepto']
                    === LotePrestamoConciliacion::CONCEPTO_GARANTE
                        ? $this->glosaGarantes(
                            $candidato['garantes'],
                            (int) $loteBloqueado->mes,
                            (int) $loteBloqueado->gestion
                        )
                        : sprintf(
                            'Descuento MinDef %02d/%04d',
                            (int) $loteBloqueado->mes,
                            (int) $loteBloqueado->gestion
                        );

                $pagoId = DB::table('pagos')->insertGetId([
                    'monto' => $monto,
                    'tipo_moneda' => $this->tipoMoneda($cuota->tipo_moneda),
                    'fecha' => $ahora->toDateString(),
                    'tipo_pago' => 'PC',
                    'anexo' => $anexo,
                    'estado' => 'AC',
                    'idlog' => $usuarioId,
                ]);

                DB::table('pagos_cuotas')->insert([
                    'id_cuota_solicitud' => $cuota->id,
                    'id_pago' => $pagoId,
                    'nro_cuota' => $cuota->nro_cuota,
                    'monto' => $monto,
                    'estado' => 'AC',
                    'idlog' => $usuarioId,
                    'fecha' => now(),
                ]);

                DB::table('lote_prestamo_pagos')->insert([
                    'lote_prestamo_procesamiento_id' => $procesamientoId,
                    'lote_prestamo_conciliacion_id' => $candidato['lote_prestamo_conciliacion_id'],
                    'lote_prestamo_conciliacion_detalle_id' => $candidato['lote_prestamo_conciliacion_detalle_id'],
                    'lote_garante_registro_id' => $candidato['lote_garante_registro_id'],
                    'id_cuota_solicitud' => $cuota->id,
                    'pago_id' => $pagoId,
                    'concepto' => $candidato['concepto'],
                    'monto' => $monto,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);

                if ($candidato['concepto']
                    === LotePrestamoConciliacion::CONCEPTO_GARANTE) {
                    LoteGaranteRegistro::query()
                        ->whereIn('id', $candidato['garantes']->pluck('id'))
                        ->update([
                            'estado_aplicacion' => LoteGaranteRegistro::APLICACION_APLICADO,
                            'pago_id' => $pagoId,
                            'updated_at' => $ahora,
                        ]);
                }

                $cantidadPagos++;
                $montoTotalCentavos += $this->aCentavos($monto);
            }

            DB::table('lote_prestamo_procesamientos')
                ->where('id', $procesamientoId)
                ->update([
                    'cantidad_pagos' => $cantidadPagos,
                    'monto_total' => $this->desdeCentavos($montoTotalCentavos),
                    'updated_at' => $ahora,
                ]);

            $solicitudesActualizadas = $this->prepararSolicitudesActualizadas(
                $cuotas
            );

            foreach ($solicitudesActualizadas as $solicitud) {
                DB::table('lote_prestamo_solicitudes')->insert([
                    'lote_prestamo_procesamiento_id' => $procesamientoId,
                    'id_solicitud' => $solicitud['id_solicitud'],
                    'ultima_cuota_anterior' => $solicitud['ultima_cuota_anterior'],
                    'saldo_actual_anterior' => $solicitud['saldo_actual_anterior'],
                    'estado_anterior' => $solicitud['estado_anterior'],
                    'ultima_cuota_nueva' => $solicitud['ultima_cuota_nueva'],
                    'saldo_actual_nuevo' => $solicitud['saldo_actual_nuevo'],
                    'estado_nuevo' => $solicitud['estado_nuevo'],
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }

            /*
             * El último de los tres módulos en finalizar cambia el lote a
             * PROCESADO, independientemente del orden de ejecución.
             */
            $this->estadoLote->sincronizar($loteBloqueado);

            /*
             * solicitudes y cuotas_solicitud son tablas Legacy/MyISAM. Sus
             * actualizaciones se ejecutan al final, cuando todos los pagos,
             * respaldos y controles InnoDB ya fueron creados correctamente.
             */
            foreach ($solicitudesActualizadas as $solicitud) {
                DB::table('solicitudes')
                    ->where(
                        'id_solicitud',
                        $solicitud['id_solicitud']
                    )
                    ->update([
                        'ultima_cuota' => $solicitud['ultima_cuota_nueva'],
                        'saldo_actual' => $solicitud['saldo_actual_nuevo'],
                        'estado' => $solicitud['estado_nuevo'],
                    ]);
            }

            DB::table('cuotas_solicitud')
                ->whereIn('id', $cuotaIds)
                ->update(['estado' => 'AC']);

            return [
                'procesamiento_id' => $procesamientoId,
                'cantidad_pagos' => $cantidadPagos,
                'monto_total' => $this->desdeCentavos($montoTotalCentavos),
                'operaciones_ignoradas' => $operacionesNoCoinciden,
                'garantes_pendientes' => $descuentosGarantesPendientes,
            ];
        }, 3);
    }

    private function validarConciliacionDisponible(LoteMensual $lote): void
    {
        $totalRegistros = DB::table('lote_prestamo_registros')
            ->where('lote_mensual_id', $lote->id)
            ->count();
        $totalAtendidos = DB::table('lote_prestamo_conciliaciones')
            ->where('lote_mensual_id', $lote->id)
            ->distinct()
            ->count('lote_prestamo_registro_id');

        if ($totalRegistros === 0 || $totalRegistros !== $totalAtendidos) {
            throw new LogicException(
                'La conciliación está incompleta. Ejecute “Volver a comparar” '
                .'antes de realizar el pago mensual.'
            );
        }
    }

    private function cargarPagosNormales(LoteMensual $lote): Collection
    {
        return DB::table('lote_prestamo_conciliaciones AS c')
            ->join(
                'lote_prestamo_conciliacion_detalles AS d',
                'd.lote_prestamo_conciliacion_id',
                '=',
                'c.id'
            )
            ->where('c.lote_mensual_id', $lote->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('c.concepto')
                    ->orWhere(
                        'c.concepto',
                        LotePrestamoConciliacion::CONCEPTO_CUOTA
                    );
            })
            ->where('c.clasificacion', LotePrestamoConciliacion::COINCIDE)
            ->orderBy('d.id')
            ->get([
                'c.id AS conciliacion_id',
                'd.id AS detalle_id',
                'd.id_cuota_solicitud',
            ])
            ->map(fn (object $fila): array => [
                'concepto' => LotePrestamoConciliacion::CONCEPTO_CUOTA,
                'id_cuota_solicitud' => (int) $fila->id_cuota_solicitud,
                'lote_prestamo_conciliacion_id' => (int) $fila->conciliacion_id,
                'lote_prestamo_conciliacion_detalle_id' => (int) $fila->detalle_id,
                'lote_garante_registro_id' => null,
                'garantes' => collect(),
            ]);
    }

    private function cargarPagosGarantes(LoteMensual $lote): Collection
    {
        $cuotaIds = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'estado_aplicacion',
                LoteGaranteRegistro::APLICACION_LISTO
            )
            ->where(
                'estado_conciliacion',
                LoteGaranteRegistro::CONCILIACION_COINCIDE
            )
            ->whereNotNull('id_cuota_solicitud')
            ->pluck('id_cuota_solicitud')
            ->unique()
            ->values();

        if ($cuotaIds->isEmpty()) {
            return collect();
        }

        $registros = LoteGaranteRegistro::query()
            ->whereIn('id_cuota_solicitud', $cuotaIds)
            ->where(
                'estado_aplicacion',
                LoteGaranteRegistro::APLICACION_LISTO
            )
            ->orderBy('id')
            ->get();

        $conciliaciones = LotePrestamoConciliacion::query()
            ->with('detalles:id,lote_prestamo_conciliacion_id')
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'concepto',
                LotePrestamoConciliacion::CONCEPTO_GARANTE
            )
            ->whereIn(
                'lote_garante_registro_id',
                $registros->pluck('id')
            )
            ->get()
            ->keyBy('lote_garante_registro_id');

        return $registros
            ->groupBy('id_cuota_solicitud')
            ->map(function (
                Collection $grupo,
                mixed $idCuota
            ) use ($conciliaciones): array {
                /** @var LoteGaranteRegistro $primero */
                $primero = $grupo->first();
                $conciliacion = $grupo
                    ->map(fn (LoteGaranteRegistro $registro) => $conciliaciones->get($registro->id))
                    ->filter()
                    ->first();

                return [
                    'concepto' => LotePrestamoConciliacion::CONCEPTO_GARANTE,
                    'id_cuota_solicitud' => (int) $idCuota,
                    'lote_prestamo_conciliacion_id' => $conciliacion?->id,
                    'lote_prestamo_conciliacion_detalle_id' => $conciliacion?->detalles?->first()?->id,
                    'lote_garante_registro_id' => $primero->id,
                    'garantes' => $grupo,
                ];
            })
            ->values();
    }

    private function validarCuotas(
        Collection $candidatos,
        Collection $cuotas
    ): void {
        $faltantes = $candidatos
            ->pluck('id_cuota_solicitud')
            ->reject(fn ($id): bool => $cuotas->has((int) $id))
            ->unique()
            ->values();

        if ($faltantes->isNotEmpty()) {
            throw new LogicException(
                'No se encontraron las cuotas ID: '
                .$faltantes->implode(', ')
                .'. No se creó ningún pago.'
            );
        }

        $noPendientes = $cuotas
            ->filter(
                fn (object $cuota): bool => strtoupper(trim((string) $cuota->estado)) !== 'PE'
            )
            ->keys();

        if ($noPendientes->isNotEmpty()) {
            throw new LogicException(
                'Las siguientes cuotas ya no están pendientes: '
                .$noPendientes->implode(', ')
                .'. Ejecute nuevamente la comparación.'
            );
        }

        foreach ($candidatos->where(
            'concepto',
            LotePrestamoConciliacion::CONCEPTO_GARANTE
        ) as $candidato) {
            $cuota = $cuotas->get($candidato['id_cuota_solicitud']);
            $acumulado = $candidato['garantes']->sum(
                fn (LoteGaranteRegistro $registro): int => $this->aCentavos($registro->monto_aplicable)
            );
            $diferencia =
                $acumulado - $this->aCentavos($cuota->cuota_fija);

            if (abs($diferencia) > self::TOLERANCIA_CENTAVOS) {
                throw new LogicException(
                    'El acumulado de garantes para la cuota ID '
                    .$cuota->id
                    .' ya no coincide con el monto de la cuota. '
                    .'Vuelva a comparar antes de consolidar.'
                );
            }
        }
    }

    private function prepararSolicitudesActualizadas(
        Collection $cuotas
    ): Collection {
        return $cuotas
            ->groupBy('id_solicitud')
            ->map(function (Collection $cuotasSolicitud): array {
                $cuotaFinal = $cuotasSolicitud
                    ->sortByDesc(
                        fn (object $cuota): int => (int) $cuota->nro_cuota
                    )
                    ->first();

                $ultimaCuota = (int) $cuotaFinal->nro_cuota;
                $periodo = (int) $cuotaFinal->periodo;

                return [
                    'id_solicitud' => (int) $cuotaFinal->id_solicitud,
                    'ultima_cuota_anterior' => (int) $cuotaFinal->ultima_cuota_solicitud,
                    'saldo_actual_anterior' => $this->desdeCentavos(
                        $this->aCentavos($cuotaFinal->saldo_actual)
                    ),
                    'estado_anterior' => trim((string) $cuotaFinal->estado_solicitud),
                    'ultima_cuota_nueva' => $ultimaCuota,
                    'saldo_actual_nuevo' => $this->desdeCentavos(
                        $this->aCentavos($cuotaFinal->saldo)
                    ),
                    'estado_nuevo' => $periodo === $ultimaCuota
                            ? 'PA'
                            : trim(
                                (string) $cuotaFinal->estado_solicitud
                            ),
                ];
            })
            ->values();
    }

    private function glosaGarantes(
        Collection $garantes,
        int $mes,
        int $gestion
    ): string {
        $papeletas = $garantes
            ->pluck('codigo_garante')
            ->map(fn ($codigo): string => trim((string) $codigo))
            ->filter()
            ->unique()
            ->values();

        return 'Descuento a garantes MinDef'
            .($papeletas->isEmpty()
                ? ''
                : ' - '.$papeletas->implode(' - '))
            .sprintf(' %02d/%04d', $mes, $gestion);
    }

    private function tipoMoneda(mixed $tipoMoneda): string
    {
        $valor = strtoupper(trim((string) $tipoMoneda));

        return $valor === 'U'
            || str_contains($valor, 'DOLAR')
            || str_contains($valor, 'USD')
                ? 'U'
                : 'B';
    }

    private function aCentavos(mixed $monto): int
    {
        return max(0, (int) round(((float) $monto) * 100));
    }

    private function desdeCentavos(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}
