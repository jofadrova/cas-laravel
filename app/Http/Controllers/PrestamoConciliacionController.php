<?php

namespace App\Http\Controllers;

use App\Models\LoteMensual;
use App\Models\LoteArchivo;
use App\Models\LoteGaranteRegistro;
use App\Models\LotePrestamoConciliacion;
use App\Models\LotePrestamoRegistro;
use App\Services\ProcesamientoMensual\PagoMensualPrestamoService;
use App\Services\ProcesamientoMensual\PrestamoConciliacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LogicException;

class PrestamoConciliacionController extends Controller
{
    public function index(Request $request, LoteMensual $lote): View
    {
        $clasificacion = $request->string('clasificacion')->upper()->toString();
        $papeleta = $this->normalizarCodigoPersonal(
            $request->string('papeleta')->toString()
        );
        $nombre = trim($request->string('nombre')->toString());

        if (! in_array(
            $clasificacion,
            LotePrestamoConciliacion::CLASIFICACIONES,
            true
        )) {
            $clasificacion = '';
        }

        $consulta = LotePrestamoConciliacion::query()
            ->with([
                'registro:id,lote_archivo_id,fila_origen,codigo_personal,carnet,nombres,monto_descuento',
                'registro.archivo:id,nombre_original',
                'detalles',
                'garanteRegistro:id,lote_archivo_id,fila_origen,codigo_titular,nombre_titular,tipo_garante,codigo_garante,nombre_garante,monto_bs,id_solicitud,id_cuota_solicitud,monto_aplicable,monto_acumulado,saldo_pendiente,estado_conciliacion,estado_aplicacion,observacion_excel,observacion_sistema',
            ])
            ->where('lote_mensual_id', $lote->id)
            ->when(
                $clasificacion !== '',
                fn ($query) => $query->where(
                    'clasificacion',
                    $clasificacion
                )
            )
            ->when(
                $papeleta !== '',
                fn ($query) => $query->whereHas(
                    'registro',
                    fn ($registro) => $registro->whereRaw(
                        <<<'SQL'
COALESCE(
    NULLIF(
        TRIM(
            LEADING '0'
            FROM TRIM(CAST(codigo_personal AS CHAR))
        ),
        ''
    ),
    '0'
) LIKE ?
SQL,
                        ["%{$papeleta}%"]
                    )
                )
            )
            ->when(
                $nombre !== '',
                fn ($query) => $query->whereHas(
                    'registro',
                    fn ($registro) => $registro->where(
                        'nombres',
                        'like',
                        "%{$nombre}%"
                    )
                )
            )
            ->orderBy('lote_prestamo_registro_id')
            ->orderBy('orden_operacion');

        $conciliaciones = $consulta
            ->paginate(50)
            ->withQueryString();

        $conteos = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('clasificacion, COUNT(*) AS total')
            ->groupBy('clasificacion')
            ->pluck('total', 'clasificacion');

        $resumen = collect(LotePrestamoConciliacion::CLASIFICACIONES)
            ->mapWithKeys(
                fn (string $estado): array => [
                    $estado => (int) ($conteos[$estado] ?? 0),
                ]
            );

        $totalImportados = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->count();
        $totalOperacionesClasificadas = $resumen->sum();
        $totalRegistrosAtendidos = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->distinct()
            ->count('lote_prestamo_registro_id');
        $archivosGarantes = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_GARANTES)
            ->latest()
            ->get();
        $registrosGarantes = LoteGaranteRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->orderBy('codigo_titular')
            ->orderBy('id')
            ->get();
        $resumenGarantes = [
            'total' => $registrosGarantes->count(),
            'monto_bs' => $registrosGarantes->sum(
                fn (LoteGaranteRegistro $registro): float =>
                    (float) $registro->monto_bs
            ),
            'pendientes' => $registrosGarantes
                ->where(
                    'estado_aplicacion',
                    LoteGaranteRegistro::APLICACION_PENDIENTE
                )
                ->count(),
            'listos' => $registrosGarantes
                ->where(
                    'estado_aplicacion',
                    LoteGaranteRegistro::APLICACION_LISTO
                )
                ->count(),
            'observados' => $registrosGarantes
                ->where(
                    'estado_aplicacion',
                    LoteGaranteRegistro::APLICACION_OBSERVADO
                )
                ->count(),
        ];
        $consultaCuotasPropias = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('concepto')
                    ->orWhere(
                        'concepto',
                        LotePrestamoConciliacion::CONCEPTO_CUOTA
                    );
            });

        $totalCuotasPropias = (clone $consultaCuotasPropias)->count();
        $cuotasCoinciden = (clone $consultaCuotasPropias)
            ->where('clasificacion', LotePrestamoConciliacion::COINCIDE)
            ->count();
        $cuotasPendientes = (clone $consultaCuotasPropias)
            ->where('clasificacion', LotePrestamoConciliacion::FALTA)
            ->count();
        $cuotasObservadas =
            $totalCuotasPropias - $cuotasCoinciden - $cuotasPendientes;
        $montoInformadoGlobal = (float) LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->sum('monto_descuento');

        /*
         * Resumen único de operaciones propias y descuentos a garantes.
         * El monto proviene solo del Excel general: el archivo de garantes
         * distribuye ese mismo descuento y sumarlo duplicaría el total.
         */
        $resumenGlobal = [
            'total' => $totalCuotasPropias + $resumenGarantes['total'],
            'monto_bs' => $montoInformadoGlobal,
            'pendientes' =>
                $cuotasPendientes + $resumenGarantes['pendientes'],
            'listos' => $cuotasCoinciden + $resumenGarantes['listos'],
            'observados' =>
                $cuotasObservadas + $resumenGarantes['observados'],
        ];
        $puedeCargarGarantes = ! in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true);
        $procesamientoPago = DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->first([
                'cantidad_pagos',
                'monto_total',
                'fecha_procesamiento',
            ]);
        $puedeRealizarPago = $procesamientoPago === null
            && ! in_array($lote->estado, [
                LoteMensual::ESTADO_PROCESADO,
                LoteMensual::ESTADO_CERRADO,
                LoteMensual::ESTADO_ANULADO,
            ], true)
            && $totalImportados > 0
            && $totalImportados === $totalRegistrosAtendidos;

        $conteoInconsistenciasPago = LotePrestamoConciliacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('clasificacion')
                    ->orWhere(
                        'clasificacion',
                        '<>',
                        LotePrestamoConciliacion::COINCIDE
                    );
            })
            ->selectRaw(
                "COALESCE(clasificacion, 'SIN_CLASIFICAR') AS estado, "
                . 'COUNT(*) AS total'
            )
            ->groupByRaw(
                "COALESCE(clasificacion, 'SIN_CLASIFICAR')"
            )
            ->pluck('total', 'estado');

        $etiquetasInconsistencias = [
            LotePrestamoConciliacion::FALTA => 'Falta',
            LotePrestamoConciliacion::DEMASIA => 'Demasía',
            LotePrestamoConciliacion::SOCIO_NO_ENCONTRADO =>
                'Socio no encontrado',
            LotePrestamoConciliacion::SIN_CUOTA => 'Sin cuota',
            LotePrestamoConciliacion::TIPO_NO_CLASIFICADO =>
                'Tipo no clasificado',
            'SIN_CLASIFICAR' => 'Sin clasificar',
        ];

        $resumenInconsistenciasPago = $conteoInconsistenciasPago
            ->map(
                fn ($total, string $estado): array => [
                    'estado' => $estado,
                    'etiqueta' => $etiquetasInconsistencias[$estado]
                        ?? str_replace('_', ' ', $estado),
                    'total' => (int) $total,
                ]
            )
            ->values();

        $totalInconsistenciasPago = $resumenInconsistenciasPago
            ->sum('total');

        $garantesPendientesPago = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'estado_conciliacion',
                LoteGaranteRegistro::CONCILIACION_COINCIDE
            )
            ->where(
                'estado_aplicacion',
                LoteGaranteRegistro::APLICACION_PENDIENTE
            )
            ->count();

        return view(
            'procesamiento-mensual.lotes.archivos.prestamos.conciliacion',
            [
                'lote' => $lote,
                'conciliaciones' => $conciliaciones,
                'clasificaciones' =>
                    LotePrestamoConciliacion::CLASIFICACIONES,
                'clasificacionSeleccionada' => $clasificacion,
                'papeletaBuscada' => $papeleta,
                'nombreBuscado' => $nombre,
                'resumen' => $resumen,
                'totalImportados' => $totalImportados,
                'totalOperacionesClasificadas' =>
                    $totalOperacionesClasificadas,
                'totalRegistrosAtendidos' => $totalRegistrosAtendidos,
                'integridadCompleta' =>
                    $totalImportados === $totalRegistrosAtendidos,
                'archivosGarantes' => $archivosGarantes,
                'registrosGarantes' => $registrosGarantes,
                'resumenGarantes' => $resumenGarantes,
                'resumenGlobal' => $resumenGlobal,
                'puedeCargarGarantes' => $puedeCargarGarantes,
                'procesamientoPago' => $procesamientoPago,
                'puedeRealizarPago' => $puedeRealizarPago,
                'resumenInconsistenciasPago' =>
                    $resumenInconsistenciasPago,
                'totalInconsistenciasPago' =>
                    $totalInconsistenciasPago,
                'garantesPendientesPago' => $garantesPendientesPago,
            ]
        );
    }

    public function comparar(
        LoteMensual $lote,
        PrestamoConciliacionService $conciliador
    ): RedirectResponse {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.index',
                    $lote
                )
                ->with(
                    'error',
                    "No es posible comparar porque el lote se encuentra {$lote->estado}."
                );
        }

        try {
            $conciliador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.index',
                    $lote
                )
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route(
                'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                $lote
            )
            ->with(
                'success',
                'La comparación terminó correctamente. Se clasificaron '
                . 'las cuotas propias y los descuentos a garantes.'
            );
    }

    public function pagar(
        LoteMensual $lote,
        PagoMensualPrestamoService $procesador
    ): RedirectResponse {
        try {
            $resultado = $procesador->ejecutar($lote, auth()->id());
        } catch (LogicException $exception) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                    $lote
                )
                ->with('error', $exception->getMessage());
        }

        $mensaje = 'Pago mensual consolidado correctamente. Se generaron '
            . number_format($resultado['cantidad_pagos'])
            . ' pagos por un total de '
            . number_format(
                (float) $resultado['monto_total'],
                2,
                ',',
                '.'
            )
            . '.';

        if ($resultado['operaciones_ignoradas'] > 0) {
            $mensaje .= ' Se ignoraron '
                . number_format($resultado['operaciones_ignoradas'])
                . ' operaciones que no coincidían; deberán resolverse '
                . 'individualmente desde “Realizar pago”.';
        }

        if ($resultado['garantes_pendientes'] > 0) {
            $mensaje .= ' Permanecen '
                . number_format($resultado['garantes_pendientes'])
                . ' descuentos a garantes pendientes de completar una cuota.';
        }

        return redirect()
            ->route(
                'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen',
                $lote
            )
            ->with('success', $mensaje);
    }

    public function resumen(
        LoteMensual $lote
    ): View|RedirectResponse {
        $procesamiento = DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->first();

        if ($procesamiento === null) {
            return redirect()
                ->route(
                    'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                    $lote
                )
                ->with(
                    'error',
                    'Este lote todavía no tiene un pago mensual consolidado.'
                );
        }

        $pagos = DB::table('lote_prestamo_pagos AS lp')
            ->join('pagos AS p', 'p.id', '=', 'lp.pago_id')
            ->join(
                'cuotas_solicitud AS cs',
                'cs.id',
                '=',
                'lp.id_cuota_solicitud'
            )
            ->join(
                'solicitudes AS s',
                's.id_solicitud',
                '=',
                'cs.id_solicitud'
            )
            ->leftJoin(
                'tasa AS t',
                't.id_tasa',
                '=',
                's.tipo_prestamo'
            )
            ->where(
                'lp.lote_prestamo_procesamiento_id',
                $procesamiento->id
            )
            ->orderBy('p.id')
            ->get([
                'p.id AS pago_id',
                'p.anexo',
                'p.monto',
                'p.tipo_moneda',
                'p.fecha',
                'lp.concepto',
                'cs.id AS id_cuota_solicitud',
                'cs.id_solicitud',
                'cs.nro_cuota',
                'cs.saldo AS saldo_cuota',
                'cs.estado AS estado_cuota',
                's.periodo',
                's.ultima_cuota',
                's.saldo_actual',
                's.estado AS estado_solicitud',
                't.descripcion_tasa',
            ]);

        $solicitudes = DB::table('lote_prestamo_solicitudes')
            ->where(
                'lote_prestamo_procesamiento_id',
                $procesamiento->id
            )
            ->orderBy('id_solicitud')
            ->get();

        $inconsistencias = DB::table(
            'lote_prestamo_conciliaciones'
        )
            ->where('lote_mensual_id', $lote->id)
            ->where(function ($query): void {
                $query
                    ->whereNull('clasificacion')
                    ->orWhere(
                        'clasificacion',
                        '<>',
                        LotePrestamoConciliacion::COINCIDE
                    );
            })
            ->selectRaw(
                "COALESCE(clasificacion, 'SIN_CLASIFICAR') AS estado, "
                . 'COUNT(*) AS total'
            )
            ->groupByRaw(
                "COALESCE(clasificacion, 'SIN_CLASIFICAR')"
            )
            ->orderBy('estado')
            ->get();

        $garantesPendientes = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'estado_conciliacion',
                LoteGaranteRegistro::CONCILIACION_COINCIDE
            )
            ->where(
                'estado_aplicacion',
                LoteGaranteRegistro::APLICACION_PENDIENTE
            )
            ->count();

        $resumenPago = [
            'pagos_normales' => $pagos
                ->where(
                    'concepto',
                    LotePrestamoConciliacion::CONCEPTO_CUOTA
                )
                ->count(),
            'pagos_garantes' => $pagos
                ->where(
                    'concepto',
                    LotePrestamoConciliacion::CONCEPTO_GARANTE
                )
                ->count(),
            'solicitudes_actualizadas' => $solicitudes->count(),
            'solicitudes_finalizadas' => $solicitudes
                ->where('estado_nuevo', 'PA')
                ->count(),
            'operaciones_ignoradas' => $inconsistencias->sum('total'),
            'garantes_pendientes' => $garantesPendientes,
        ];

        return view(
            'procesamiento-mensual.lotes.archivos.prestamos.resumen-pago',
            [
                'lote' => $lote,
                'procesamiento' => $procesamiento,
                'pagos' => $pagos,
                'solicitudes' => $solicitudes,
                'inconsistencias' => $inconsistencias,
                'resumenPago' => $resumenPago,
            ]
        );
    }

    private function normalizarCodigoPersonal(string $valor): string
    {
        $valor = trim($valor);

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
}
