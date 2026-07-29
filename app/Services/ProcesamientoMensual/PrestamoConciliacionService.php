<?php

namespace App\Services\ProcesamientoMensual;

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

        $institucionesPorPapeleta = $this->cargarInstituciones($registros);
        $cuotasPorSocio = $this->cargarCuotas($lote);
        $ahora = now();
        $resultados = [];
        $detalles = [];

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

            $cuotas = $cuotasPorSocio
                ->get((string) $institucion->id_socio, collect())
                ->values();

            if ($cuotas->isEmpty()) {
                $resultado = $this->nuevoResultado(
                    $lote,
                    $registro,
                    1,
                    $usuarioId,
                    $ahora
                );
                $resultado['socio_institucion_id'] = $institucion->id;
                $resultado['id_socio'] = $institucion->id_socio;
                $resultado['clasificacion'] =
                    LotePrestamoConciliacion::SIN_CUOTA;
                $resultado['observacion'] =
                    'El socio no tiene cuotas PE de solicitudes AC en el periodo del lote.';
                $resultados[] = $resultado;
                continue;
            }

            /*
             * El Excel entrega un total por papeleta. Se distribuye entre las
             * cuotas independientes del socio, ordenadas por tipo de préstamo.
             * Las primeras operaciones reciben como máximo el importe exacto
             * exigido por su cuota. La última recibe todo el remanente, incluida
             * cualquier falta o demasía.
             */
            $saldoExcelCentavos = $this->aCentavos(
                $registro->monto_descuento
            );
            $ultimaPosicion = $cuotas->count() - 1;

            foreach ($cuotas as $posicion => $cuota) {
                $ordenOperacion = $posicion + 1;
                $factor = $this->factorConversion($cuota->id_tasa);
                $montoCuotaCentavos = $this->aCentavos(
                    $cuota->monto_cuota_pagar
                );
                $montoExigidoExcelCentavos = $this->montoOrigenCentavos(
                    $montoCuotaCentavos,
                    $factor
                );

                if ($posicion === $ultimaPosicion) {
                    $montoAsignadoExcelCentavos = $saldoExcelCentavos;
                } else {
                    $montoAsignadoExcelCentavos = min(
                        $saldoExcelCentavos,
                        $montoExigidoExcelCentavos
                    );
                }

                $saldoExcelCentavos = max(
                    0,
                    $saldoExcelCentavos - $montoAsignadoExcelCentavos
                );

                $montoComparableCentavos = $this->montoComparableCentavos(
                    $montoAsignadoExcelCentavos,
                    $factor
                );
                $diferenciaCentavos =
                    $montoComparableCentavos - $montoCuotaCentavos;
                $grupo = $this->clasificarGrupo($cuota);

                $resultado = $this->nuevoResultado(
                    $lote,
                    $registro,
                    $ordenOperacion,
                    $usuarioId,
                    $ahora
                );
                $resultado['socio_institucion_id'] = $institucion->id;
                $resultado['id_socio'] = $institucion->id_socio;
                $resultado['monto_excel'] =
                    $this->desdeCentavos($montoComparableCentavos);
                $resultado['monto_excel_asignado'] =
                    $this->desdeCentavos($montoAsignadoExcelCentavos);
                $resultado['monto_base_datos'] =
                    $this->desdeCentavos($montoCuotaCentavos);
                $resultado['diferencia'] =
                    $this->desdeCentavos($diferenciaCentavos);
                $resultado['cantidad_cuotas'] = 1;

                if (! $this->tipoTieneRegla($cuota->id_tasa)) {
                    $resultado['clasificacion'] =
                        LotePrestamoConciliacion::TIPO_NO_CLASIFICADO;
                    $resultado['observacion'] =
                        'El tipo de préstamo '
                        . ($cuota->tipo_prestamo ?? 'no definido')
                        . ' no tiene una regla de conversión configurada.';
                } elseif (
                    abs($diferenciaCentavos) <= self::TOLERANCIA_CENTAVOS
                ) {
                    $resultado['clasificacion'] =
                        LotePrestamoConciliacion::COINCIDE;
                    $resultado['observacion'] =
                        'La porción asignada del total Excel coincide con '
                        . 'cuotas_solicitud.cuota_fija.'
                        . $this->descripcionConversion($cuota->id_tasa);
                } elseif ($diferenciaCentavos < 0) {
                    $resultado['clasificacion'] =
                        LotePrestamoConciliacion::FALTA;
                    $resultado['observacion'] =
                        'La porción asignada del total Excel es menor que '
                        . 'cuotas_solicitud.cuota_fija.'
                        . $this->descripcionConversion($cuota->id_tasa);
                } else {
                    $resultado['clasificacion'] =
                        LotePrestamoConciliacion::DEMASIA;
                    $resultado['observacion'] =
                        'El remanente asignado a la última operación es mayor '
                        . 'que cuotas_solicitud.cuota_fija.'
                        . $this->descripcionConversion($cuota->id_tasa);
                }

                $resultados[] = $resultado;
                $detalles[$this->claveResultado(
                    $registro->id,
                    $ordenOperacion
                )] = [
                    'id_solicitud' => $cuota->id_solicitud,
                    'id_cuota_solicitud' => $cuota->id_cuota_solicitud,
                    'tipo_prestamo' => $cuota->tipo_prestamo,
                    'descripcion_tipo' => $cuota->descripcion_tasa,
                    'grupo_comparacion' => $grupo,
                    'nro_cuota' => $cuota->nro_cuota,
                    'monto_cuota' => $this->desdeCentavos(
                        $montoCuotaCentavos
                    ),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        $this->validarResultados($registros, $resultados);

        DB::transaction(function () use (
            $lote,
            $resultados,
            $detalles
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
                    fn (LotePrestamoConciliacion $conciliacion): string =>
                        $this->claveResultado(
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
        });
    }

    private function cargarInstituciones(Collection $registros): Collection
    {
        $papeletas = $registros
            ->pluck('codigo_personal')
            ->map(
                fn ($valor): string =>
                    $this->normalizarCodigoPersonal($valor)
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
                fn (object $institucion): string =>
                    $this->normalizarCodigoPersonal($institucion->papeleta)
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
                    (strtoupper(trim((string) $institucion->estado)) === 'AC'
                        ? '1'
                        : '0')
                    . str_pad((string) $institucion->id, 20, '0', STR_PAD_LEFT)
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
            'eit_item' => null,
            'socio_institucion_id' => null,
            'id_socio' => null,
            'monto_excel' => $this->desdeCentavos($montoExcelCentavos),
            'monto_excel_asignado' =>
                $this->desdeCentavos($montoExcelCentavos),
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

    private function cargarCuotas(LoteMensual $lote): Collection
    {
        return DB::table('cuotas_solicitud AS cs')
            ->join(
                'solicitudes AS s',
                's.id_solicitud',
                '=',
                'cs.id_solicitud'
            )
            ->leftJoin('tasa AS t', 't.id_tasa', '=', 's.tipo_prestamo')
            ->where('cs.mes', (int) $lote->mes)
            ->where('cs.gestion', (int) $lote->gestion)
            ->where('cs.estado', 'PE')
            ->whereIn('s.estado', ['AC', 'DI'])
            ->select([
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
            ->orderBy('s.ide_per')
            ->orderBy('s.tipo_prestamo')
            ->orderBy('s.id_solicitud')
            ->orderBy('cs.nro_cuota')
            ->get()
            ->groupBy(fn (object $cuota): string => (string) $cuota->id_socio);
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
            . number_format($factor, 2, '.', '')
            . ' por corresponder al tipo de préstamo '
            . $idTasa
            . '.';
    }

    private function claveResultado(
        mixed $registroId,
        mixed $ordenOperacion
    ): string {
        return (string) $registroId . ':' . (string) $ordenOperacion;
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