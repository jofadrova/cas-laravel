<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use App\Models\LotePrestamoConciliacion;
use App\Models\LotePrestamoConciliacionDetalle;
use App\Models\LotePrestamoRegistro;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class PrestamoConciliacionService
{
    private const TOLERANCIA_CENTAVOS = 1;

    private const FACTORES_POR_TIPO = [
        1 => 6.96,
        2 => 1.00,
        3 => 6.97,
        4 => 6.96,
        5 => 6.96,
        6 => 6.96,
        7 => 6.96,
    ];

    public function __construct(
        private readonly GaranteConciliacionService $conciliadorGarantes
    ) {}

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): void
    {
        $registros = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('id')
            ->get([
                'id',
                'codigo_personal',
                'monto_descuento',
            ]);

        if ($registros->isEmpty()) {
            throw new LogicException(
                'El lote no contiene registros de préstamos para comparar.'
            );
        }

        $this->conciliadorGarantes->ejecutar($lote, $usuarioId);
        $garantes = $this->cargarDescuentosGarantes($lote);
        $garantesPorPapeleta = $garantes->groupBy(
            fn (LoteGaranteRegistro $registro): string => $this->normalizarCodigoPersonal($registro->codigo_garante)
        );
        $idsRegistrosPrincipales = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->whereHas(
                'archivo',
                fn ($query) => $query->where('ruta', 'not like', '%/otros/%')
            )
            ->pluck('id')
            ->flip();
        $cuotasObjetivoGarantes = $this->cargarCuotasObjetivoGarantes(
            $garantes
        );
        $institucionesPorPapeleta = $this->cargarInstituciones($registros);
        $cuotasPorPapeleta = $this->cargarCuotasPorPapeleta(
            $lote,
            $institucionesPorPapeleta->keys()
        );
        $ahora = now();
        $resultados = [];
        $detalles = [];
        $clasificacionesGarantes = [];
        $montosExcelGarantes = [];

        foreach ($registros as $registro) {
            $clave = $this->normalizarCodigoPersonal(
                $registro->codigo_personal
            );
            $instituciones = $institucionesPorPapeleta->get($clave, collect());
            $institucion = $this->resolverInstitucion($instituciones);

            if ($institucion === null) {
                $resultado = $this->nuevoResultado(
                    $lote,
                    $registro,
                    1,
                    $usuarioId,
                    $ahora
                );
                $resultado['clasificacion'] =
                    LotePrestamoConciliacion::SOCIO_NO_ENCONTRADO;
                $resultado['observacion'] =
                    'CODIGO_PERSONAL no fue encontrado en socio_institucion.papeleta.';
                $resultados[] = $resultado;

                continue;
            }

            /*
             * La papeleta del archivo es la llave operativa del descuento.
             * La consulta ya recorrió todas las filas históricas de
             * socio_institucion asociadas a esa papeleta. Solo eliminamos
             * duplicados de la misma cuota que puedan producir esas filas.
             */
            $cuotas = $cuotasPorPapeleta
                ->get($clave, collect())
                ->unique('id_cuota_solicitud')
                ->sortBy([
                    ['tipo_prestamo', 'asc'],
                    ['id_solicitud', 'asc'],
                    ['nro_cuota', 'asc'],
                ])
                ->values();
            $institucionResultado = $cuotas->isEmpty()
                ? $institucion
                : (
                    $instituciones->firstWhere(
                        'id_socio',
                        $cuotas->first()->id_socio
                    ) ?? $institucion
                );
            $descuentosGarante = $idsRegistrosPrincipales->has($registro->id)
                ? $garantesPorPapeleta->get($clave, collect())->values()
                : collect();
            $operaciones = $this->crearOperaciones(
                $cuotas,
                $descuentosGarante,
                $cuotasObjetivoGarantes
            );

            if ($operaciones->isEmpty()) {
                $resultado = $this->nuevoResultado(
                    $lote,
                    $registro,
                    1,
                    $usuarioId,
                    $ahora
                );
                $resultado['socio_institucion_id'] =
                    $institucionResultado->id;
                $resultado['id_socio'] =
                    $institucionResultado->id_socio;
                $resultado['clasificacion'] =
                    LotePrestamoConciliacion::SIN_CUOTA;
                $resultado['observacion'] =
                    'El socio no tiene cuotas PE del periodo ni descuentos '
                    .'a garante registrados en el lote.';
                $resultados[] = $resultado;

                continue;
            }

            $saldoExcelCentavos = $this->aCentavos(
                $registro->monto_descuento
            );
            $ultimaPosicion = $operaciones->count() - 1;

            foreach ($operaciones as $posicion => $operacion) {
                $ordenOperacion = $posicion + 1;
                $montoExigidoOrigen = $operacion['monto_origen_centavos'];

                if ($posicion === $ultimaPosicion) {
                    $montoAsignadoOrigen = $saldoExcelCentavos;
                } else {
                    $montoAsignadoOrigen = min(
                        $saldoExcelCentavos,
                        $montoExigidoOrigen
                    );
                }

                $saldoExcelCentavos = max(
                    0,
                    $saldoExcelCentavos - $montoAsignadoOrigen
                );
                $montoComparable = $operacion['concepto']
                    === LotePrestamoConciliacion::CONCEPTO_GARANTE
                        ? $montoAsignadoOrigen
                        : $this->montoComparableCentavos(
                            $montoAsignadoOrigen,
                            $operacion['factor']
                        );
                $diferencia =
                    $montoComparable - $operacion['monto_base_centavos'];

                $resultado = $this->nuevoResultado(
                    $lote,
                    $registro,
                    $ordenOperacion,
                    $usuarioId,
                    $ahora
                );
                $resultado['concepto'] = $operacion['concepto'];
                $resultado['lote_garante_registro_id'] =
                    $operacion['garante']?->id;
                $resultado['socio_institucion_id'] =
                    $institucionResultado->id;
                $resultado['id_socio'] =
                    $institucionResultado->id_socio;
                $resultado['monto_excel'] =
                    $this->desdeCentavos($montoComparable);
                $resultado['monto_excel_asignado'] =
                    $this->desdeCentavos($montoAsignadoOrigen);
                $resultado['monto_base_datos'] =
                    $this->desdeCentavos(
                        $operacion['monto_base_centavos']
                    );
                $resultado['diferencia'] =
                    $this->desdeCentavos($diferencia);
                $resultado['cantidad_cuotas'] = 1;

                $this->clasificarOperacion(
                    $resultado,
                    $operacion,
                    $diferencia
                );
                $resultados[] = $resultado;

                if ($operacion['garante'] !== null) {
                    $clasificacionesGarantes[$operacion['garante']->id] =
                        $resultado['clasificacion'];
                    $montosExcelGarantes[$operacion['garante']->id] =
                        ($montosExcelGarantes[$operacion['garante']->id] ?? 0)
                        + $montoAsignadoOrigen;
                }

                $detalle = $this->crearDetalleOperacion(
                    $operacion,
                    $ahora
                );

                if ($detalle !== null) {
                    $detalles[$this->claveResultado(
                        $registro->id,
                        $ordenOperacion
                    )] = $detalle;
                }
            }
        }

        $this->validarDescuentosGarantesAsignados(
            $registros->filter(
                fn (LotePrestamoRegistro $registro): bool => $idsRegistrosPrincipales->has($registro->id)
            ),
            $garantes,
            $clasificacionesGarantes
        );
        $this->validarResultados($registros, $resultados);

        DB::transaction(function () use (
            $lote,
            $resultados,
            $detalles,
            $garantes,
            $clasificacionesGarantes,
            $montosExcelGarantes
        ): void {
            LotePrestamoConciliacion::query()
                ->where('lote_mensual_id', $lote->id)
                ->delete();

            foreach (array_chunk($resultados, 500) as $bloque) {
                LotePrestamoConciliacion::insert($bloque);
            }

            $conciliaciones = LotePrestamoConciliacion::query()
                ->where('lote_mensual_id', $lote->id)
                ->get([
                    'id',
                    'lote_prestamo_registro_id',
                    'orden_operacion',
                ])
                ->keyBy(
                    fn (LotePrestamoConciliacion $conciliacion): string => $this->claveResultado(
                        $conciliacion->lote_prestamo_registro_id,
                        $conciliacion->orden_operacion
                    )
                );

            $filasDetalle = [];

            foreach ($detalles as $clave => $detalle) {
                $conciliacion = $conciliaciones->get($clave);

                if ($conciliacion === null) {
                    throw new LogicException(
                        "No se generó la conciliación de la operación {$clave}."
                    );
                }

                $filasDetalle[] = [
                    ...$detalle,
                    'lote_prestamo_conciliacion_id' => $conciliacion->id,
                ];
            }

            foreach (array_chunk($filasDetalle, 500) as $bloque) {
                LotePrestamoConciliacionDetalle::insert($bloque);
            }

            LoteGaranteRegistro::query()
                ->whereIn('id', $garantes->pluck('id'))
                ->update([
                    'estado_conciliacion' => LoteGaranteRegistro::CONCILIACION_SIN_DESCUENTO,
                    'updated_at' => now(),
                ]);

            foreach ($clasificacionesGarantes as $id => $clasificacion) {
                LoteGaranteRegistro::query()
                    ->whereKey($id)
                    ->update([
                        'estado_conciliacion' => $clasificacion,
                        'updated_at' => now(),
                    ]);
            }

            $this->conciliadorGarantes->actualizarMontosDesdeExcel(
                $garantes,
                $montosExcelGarantes
            );
        });
    }

    private function crearOperaciones(
        Collection $cuotas,
        Collection $descuentosGarante,
        Collection $cuotasObjetivoGarantes
    ): Collection {
        $operaciones = collect();

        foreach ($cuotas as $cuota) {
            $factor = $this->factorConversion($cuota->id_tasa);
            $montoBase = $this->aCentavos($cuota->monto_cuota_pagar);

            $operaciones->push([
                'concepto' => LotePrestamoConciliacion::CONCEPTO_CUOTA,
                'factor' => $factor,
                'monto_base_centavos' => $montoBase,
                'monto_origen_centavos' => $this->montoOrigenCentavos($montoBase, $factor),
                'cuota' => $cuota,
                'garante' => null,
                'cuota_objetivo' => null,
            ]);
        }

        foreach ($descuentosGarante as $descuento) {
            $montoBs = $this->aCentavos($descuento->monto_bs);

            $operaciones->push([
                'concepto' => LotePrestamoConciliacion::CONCEPTO_GARANTE,
                'factor' => 1.00,
                'monto_base_centavos' => $montoBs,
                'monto_origen_centavos' => $montoBs,
                'cuota' => null,
                'garante' => $descuento,
                'cuota_objetivo' => $cuotasObjetivoGarantes->get(
                    (int) $descuento->id_cuota_solicitud
                ),
            ]);
        }

        return $operaciones;
    }

    private function cargarDescuentosGarantes(
        LoteMensual $lote
    ): Collection {
        return LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'estado_aplicacion',
                '<>',
                LoteGaranteRegistro::APLICACION_ANULADO
            )
            ->orderBy('id')
            ->get();
    }

    private function validarDescuentosGarantesAsignados(
        Collection $registros,
        Collection $garantes,
        array $clasificacionesGarantes
    ): void {
        if ($garantes->isEmpty()) {
            return;
        }

        $codigosExcel = $registros
            ->pluck('codigo_personal')
            ->map(
                fn ($codigo): string => $this->normalizarCodigoPersonal($codigo)
            )
            ->filter(fn (string $codigo): bool => $codigo !== '')
            ->unique()
            ->flip();

        $omitidos = $garantes
            ->filter(
                fn (LoteGaranteRegistro $registro): bool => $codigosExcel->has(
                    $this->normalizarCodigoPersonal(
                        $registro->codigo_garante
                    )
                )
                    && ! array_key_exists(
                        $registro->id,
                        $clasificacionesGarantes
                    )
            );

        if ($omitidos->isEmpty()) {
            return;
        }

        $papeletas = $omitidos
            ->pluck('codigo_garante')
            ->map(
                fn ($codigo): string => $this->normalizarCodigoPersonal($codigo)
            )
            ->unique()
            ->sort()
            ->implode(', ');

        throw new LogicException(
            'No fue posible incorporar como DESCUENTO A GARANTE los '
            ."registros de las papeletas: {$papeletas}. "
            .'La comparación fue cancelada para evitar clasificarlos '
            .'incorrectamente como demasía.'
        );
    }

    private function clasificarOperacion(
        array &$resultado,
        array $operacion,
        int $diferenciaCentavos
    ): void {
        if ($operacion['concepto']
            === LotePrestamoConciliacion::CONCEPTO_CUOTA
            && ! $this->tipoTieneRegla($operacion['cuota']->id_tasa)) {
            $resultado['clasificacion'] =
                LotePrestamoConciliacion::TIPO_NO_CLASIFICADO;
            $resultado['observacion'] =
                'El tipo de préstamo '
                .($operacion['cuota']->tipo_prestamo ?? 'no definido')
                .' no tiene una regla de conversión configurada.';

            return;
        }

        if (abs($diferenciaCentavos) <= self::TOLERANCIA_CENTAVOS) {
            $resultado['clasificacion'] =
                LotePrestamoConciliacion::COINCIDE;
        } elseif ($diferenciaCentavos < 0) {
            $resultado['clasificacion'] =
                LotePrestamoConciliacion::FALTA;
        } else {
            $resultado['clasificacion'] =
                LotePrestamoConciliacion::DEMASIA;
        }

        if ($operacion['concepto']
            === LotePrestamoConciliacion::CONCEPTO_GARANTE) {
            $resultado['observacion'] = match ($resultado['clasificacion']) {
                LotePrestamoConciliacion::COINCIDE => 'La porción del Excel general coincide con el '
                    .'DESCUENTO A GARANTE informado por Cartera de Crédito.',
                LotePrestamoConciliacion::FALTA => 'La porción del Excel general es menor que el '
                    .'DESCUENTO A GARANTE informado.',
                default => 'El remanente del Excel general es mayor que el '
                    .'DESCUENTO A GARANTE informado.',
            };

            return;
        }

        $resultado['observacion'] = match ($resultado['clasificacion']) {
            LotePrestamoConciliacion::COINCIDE => 'La porción asignada del total Excel coincide con '
                .'cuotas_solicitud.cuota_fija.'
                .$this->descripcionConversion(
                    $operacion['cuota']->id_tasa
                ),
            LotePrestamoConciliacion::FALTA => 'La porción asignada del total Excel es menor que '
                .'cuotas_solicitud.cuota_fija.'
                .$this->descripcionConversion(
                    $operacion['cuota']->id_tasa
                ),
            default => 'El remanente asignado a la última operación es mayor '
                .'que cuotas_solicitud.cuota_fija.'
                .$this->descripcionConversion(
                    $operacion['cuota']->id_tasa
                ),
        };
    }

    private function crearDetalleOperacion(
        array $operacion,
        mixed $ahora
    ): ?array {
        if ($operacion['concepto']
            === LotePrestamoConciliacion::CONCEPTO_CUOTA) {
            $cuota = $operacion['cuota'];

            return [
                'id_solicitud' => $cuota->id_solicitud,
                'id_cuota_solicitud' => $cuota->id_cuota_solicitud,
                'tipo_prestamo' => $cuota->tipo_prestamo,
                'descripcion_tipo' => $cuota->descripcion_tasa,
                'grupo_comparacion' => $this->clasificarGrupo($cuota),
                'nro_cuota' => $cuota->nro_cuota,
                'monto_cuota' => $this->desdeCentavos(
                    $operacion['monto_base_centavos']
                ),
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        $garante = $operacion['garante'];
        $cuotaObjetivo = $operacion['cuota_objetivo'];

        if ($garante?->id_solicitud === null
            || $garante?->id_cuota_solicitud === null
            || $cuotaObjetivo === null) {
            return null;
        }

        return [
            'id_solicitud' => $garante->id_solicitud,
            'id_cuota_solicitud' => $garante->id_cuota_solicitud,
            'tipo_prestamo' => 1,
            'descripcion_tipo' => 'REGULAR · DESCUENTO A GARANTE',
            'grupo_comparacion' => 'REGULAR_GARANTE',
            'nro_cuota' => $cuotaObjetivo->nro_cuota,
            'monto_cuota' => number_format(
                (float) $cuotaObjetivo->cuota_fija,
                2,
                '.',
                ''
            ),
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ];
    }

    private function cargarInstituciones(Collection $registros): Collection
    {
        $papeletas = $registros
            ->pluck('codigo_personal')
            ->map(
                fn ($valor): string => $this->normalizarCodigoPersonal($valor)
            )
            ->filter(fn (string $valor): bool => $valor !== '')
            ->unique()
            ->values();

        if ($papeletas->isEmpty()) {
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
            ->whereIn(DB::raw($expresion), $papeletas->all())
            ->get()
            ->groupBy(
                fn (object $institucion): string => $this->normalizarCodigoPersonal($institucion->papeleta)
            );
    }

    private function resolverInstitucion(Collection $instituciones): ?object
    {
        if ($instituciones->isEmpty()) {
            return null;
        }

        return $instituciones
            ->sortByDesc(
                fn (object $institucion): string => (strtoupper(trim((string) $institucion->estado)) === 'AC'
                        ? '1'
                        : '0')
                    .str_pad((string) $institucion->id, 20, '0', STR_PAD_LEFT)
            )
            ->first();
    }

    private function nuevoResultado(
        LoteMensual $lote,
        LotePrestamoRegistro $registro,
        int $ordenOperacion,
        ?int $usuarioId,
        mixed $ahora
    ): array {
        $montoExcelCentavos = $this->aCentavos($registro->monto_descuento);

        return [
            'lote_mensual_id' => $lote->id,
            'lote_prestamo_registro_id' => $registro->id,
            'orden_operacion' => $ordenOperacion,
            'concepto' => LotePrestamoConciliacion::CONCEPTO_CUOTA,
            'lote_garante_registro_id' => null,
            'eit_item' => null,
            'socio_institucion_id' => null,
            'id_socio' => null,
            'monto_excel' => $this->desdeCentavos($montoExcelCentavos),
            'monto_excel_asignado' => $this->desdeCentavos($montoExcelCentavos),
            'monto_base_datos' => '0.00',
            'diferencia' => $this->desdeCentavos($montoExcelCentavos),
            'clasificacion' => null,
            'cantidad_cuotas' => 0,
            'observacion' => null,
            'conciliado_por' => $usuarioId,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ];
    }

    private function cargarCuotasPorPapeleta(
        LoteMensual $lote,
        Collection $papeletas
    ): Collection {
        if ($papeletas->isEmpty()) {
            return collect();
        }

        $expresionPapeleta = <<<'SQL'
COALESCE(
    NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(si.papeleta AS CHAR))), ''),
    '0'
)
SQL;

        return DB::table('cuotas_solicitud AS cs')
            ->join(
                'solicitudes AS s',
                's.id_solicitud',
                '=',
                'cs.id_solicitud'
            )
            ->join(
                'socio_institucion AS si',
                'si.id_socio',
                '=',
                's.ide_per'
            )
            ->leftJoin('tasa AS t', 't.id_tasa', '=', 's.tipo_prestamo')
            ->whereIn(DB::raw($expresionPapeleta), $papeletas->all())
            ->where('cs.mes', (int) $lote->mes)
            ->where('cs.gestion', (int) $lote->gestion)
            ->whereRaw("UPPER(TRIM(cs.estado)) = 'PE'")
            ->whereRaw("UPPER(TRIM(s.estado)) IN ('AC', 'DI')")
            ->select([
                'si.papeleta',
                's.ide_per AS id_socio',
                's.id_solicitud',
                's.tipo_prestamo',
                's.tipo_prestamo AS id_tasa',
                'cs.id AS id_cuota_solicitud',
                'cs.nro_cuota',
                'cs.cuota_fija AS monto_cuota_pagar',
                't.descripcion_tasa',
                't.cod_desc',
                't.tipo_tasa',
            ])
            ->orderBy('si.papeleta')
            ->orderBy('s.ide_per')
            ->orderBy('s.tipo_prestamo')
            ->orderBy('s.id_solicitud')
            ->orderBy('cs.nro_cuota')
            ->get()
            ->groupBy(
                fn (object $cuota): string => $this->normalizarCodigoPersonal($cuota->papeleta)
            );
    }

    private function cargarCuotasObjetivoGarantes(
        Collection $garantes
    ): Collection {
        $ids = $garantes
            ->pluck('id_cuota_solicitud')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('cuotas_solicitud')
            ->whereIn('id', $ids)
            ->get(['id', 'nro_cuota', 'cuota_fija', 'mes', 'gestion'])
            ->keyBy('id');
    }

    private function validarResultados(
        Collection $registros,
        array $resultados
    ): void {
        $registroIds = $registros
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->sort()
            ->values()
            ->all();
        $registroIdsClasificados = collect($resultados)
            ->pluck('lote_prestamo_registro_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($registroIds !== $registroIdsClasificados) {
            throw new LogicException(
                'No fue posible atender la totalidad de los registros importados.'
            );
        }

        foreach ($resultados as $resultado) {
            if (! in_array(
                $resultado['clasificacion'],
                LotePrestamoConciliacion::CLASIFICACIONES,
                true
            )) {
                throw new LogicException(
                    'Se generó una operación sin una clasificación válida.'
                );
            }
        }
    }

    private function clasificarGrupo(object $cuota): ?string
    {
        $texto = $this->normalizarTexto(implode(' ', array_filter([
            $cuota->cod_desc,
            $cuota->tipo_tasa,
            $cuota->descripcion_tasa,
        ])));

        if ($texto === '') {
            return null;
        }

        if (str_contains($texto, 'GARANT')) {
            return 'GARANTIAS';
        }

        if (str_contains($texto, 'CREDINAMIC')
            || str_contains($texto, 'CREDI DINAMIC')) {
            return 'CREDINAMIC';
        }

        if (str_contains($texto, 'EMERGEN')) {
            return 'EMERGENCIA';
        }

        if (str_contains($texto, 'CONSUMO')) {
            return 'CONSUMO';
        }

        if (str_contains($texto, 'AUXILIO')
            && (
                str_contains($texto, 'DOLAR')
                || str_contains($texto, 'USD')
                || str_contains($texto, '$US')
            )) {
            return 'AUXILIO_DOLARES';
        }

        if (str_contains($texto, 'AUXILIO')) {
            return 'AUXILIO';
        }

        if (str_contains($texto, 'REGULAR')) {
            return 'REGULAR';
        }

        return null;
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

        return $this->normalizarTexto($valor);
    }

    private function normalizarTexto(?string $valor): string
    {
        return preg_replace(
            '/\s+/',
            ' ',
            Str::upper(Str::ascii(trim((string) $valor)))
        ) ?? '';
    }

    private function tipoTieneRegla(mixed $idTasa): bool
    {
        return array_key_exists((int) $idTasa, self::FACTORES_POR_TIPO);
    }

    private function factorConversion(mixed $idTasa): float
    {
        return self::FACTORES_POR_TIPO[(int) $idTasa] ?? 1.00;
    }

    private function montoOrigenCentavos(
        int $montoCuotaCentavos,
        float $factor
    ): int {
        return (int) round($montoCuotaCentavos * $factor);
    }

    private function montoComparableCentavos(
        int $montoOrigenCentavos,
        float $factor
    ): int {
        return (int) round($montoOrigenCentavos / $factor);
    }

    private function descripcionConversion(mixed $idTasa): string
    {
        $idTasa = (int) $idTasa;
        $factor = $this->factorConversion($idTasa);

        if ($factor === 1.00) {
            return ' Tipo de préstamo 2: no se aplicó conversión.';
        }

        return ' La porción asignada fue dividida entre '
            .number_format($factor, 2, '.', '')
            .' por corresponder al tipo de préstamo '
            .$idTasa
            .'.';
    }

    private function claveResultado(
        mixed $registroId,
        mixed $ordenOperacion
    ): string {
        return (string) $registroId.':'.(string) $ordenOperacion;
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
