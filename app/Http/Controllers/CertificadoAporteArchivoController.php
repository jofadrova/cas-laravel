<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificadoAporteArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteCertificadoAporteRegistro;
use App\Models\LoteCertificadoAporteSeparacion;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\CertificadoAporteExcelImportService;
use App\Services\ProcesamientoMensual\EstadoLoteMensualService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class CertificadoAporteArchivoController extends Controller
{
    public function index(Request $request, LoteMensual $lote): View
    {
        $todosLosArchivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
            ->orderBy('id')
            ->get();
        $totalesPorArchivo = LoteCertificadoAporteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('lote_archivo_id')
            ->selectRaw('COALESCE(SUM(tasa_regulacion), 0) AS tasa_regulacion')
            ->selectRaw('COALESCE(SUM(total_descuento), 0) AS total_descuento')
            ->groupBy('lote_archivo_id')
            ->get()
            ->keyBy('lote_archivo_id');
        $todosLosArchivos->each(function (LoteArchivo $archivo) use ($totalesPorArchivo): void {
            $totales = $totalesPorArchivo->get($archivo->id);
            $archivo->setAttribute('total_tasa_regulacion', $totales?->tasa_regulacion ?? 0);
            $archivo->setAttribute('total_descuento_calculado', $totales?->total_descuento ?? 0);
        });
        $archivos = $todosLosArchivos
            ->reject(fn (LoteArchivo $archivo): bool => str_contains(
                (string) $archivo->ruta,
                '/otros/'
            ))
            ->values();
        $otrosArchivos = $todosLosArchivos
            ->filter(fn (LoteArchivo $archivo): bool => str_contains(
                (string) $archivo->ruta,
                '/otros/'
            ))
            ->values();

        $buscar = trim((string) $request->query('buscar', ''));

        $consulta = LoteCertificadoAporteRegistro::query()
            ->with('archivo:id,nombre_original')
            ->where('lote_mensual_id', $lote->id)
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(function ($subconsulta) use ($buscar): void {
                    $subconsulta
                        ->where('codigo_personal', 'like', "%{$buscar}%")
                        ->orWhere('eit_item', 'like', "%{$buscar}%")
                        ->orWhere('carnet', 'like', "%{$buscar}%")
                        ->orWhere('nombres', 'like', "%{$buscar}%")
                        ->orWhereHas(
                            'archivo',
                            fn ($archivo) => $archivo->where(
                                'nombre_original',
                                'like',
                                "%{$buscar}%"
                            )
                        );
                });
            })
            ->orderBy('lote_archivo_id')
            ->orderBy('fila_origen');

        $registros = $consulta->paginate(50)->withQueryString();

        $resumen = LoteCertificadoAporteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->selectRaw('COUNT(*) AS filas')
            ->selectRaw('COALESCE(SUM(monto_descuento), 0) AS monto_descuento')
            ->selectRaw('COALESCE(SUM(tot_2), 0) AS tot_2')
            ->selectRaw('COALESCE(SUM(comision), 0) AS comision')
            ->selectRaw('COALESCE(SUM(tasa_regulacion), 0) AS tasa_regulacion')
            ->selectRaw('COALESCE(SUM(total_descuento), 0) AS total_descuento')
            ->first();
        $registrosSeparados = LoteCertificadoAporteSeparacion::query()
            ->where('lote_mensual_id', $lote->id)
            ->count();

        $puedeModificar = ! in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true);

        return view('procesamiento-mensual.lotes.certificados.index', [
            'lote' => $lote,
            'archivos' => $archivos,
            'otrosArchivos' => $otrosArchivos,
            'registros' => $registros,
            'resumen' => $resumen,
            'buscar' => $buscar,
            'puedeModificar' => $puedeModificar,
            'puedeCargar' => $puedeModificar && $archivos->count() < 10,
            'cantidadMinimaPendiente' => max(1, 3 - $archivos->count()),
            'cantidadDisponible' => max(0, 10 - $archivos->count()),
            'registrosSeparados' => $registrosSeparados,
        ]);
    }

    public function store(
        StoreCertificadoAporteArchivoRequest $request,
        LoteMensual $lote,
        CertificadoAporteExcelImportService $importador,
        EstadoLoteMensualService $estadoLote
    ): RedirectResponse {
        /** @var array<int, UploadedFile> $archivos */
        $archivos = $request->file('archivos', []);
        $lecturas = [];
        $hashesDelGrupo = [];

        foreach ($archivos as $indice => $archivo) {
            try {
                $lectura = $importador->leer($archivo, $lote);
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    "archivos.{$indice}" => $archivo->getClientOriginalName()
                        .': '.$exception->getMessage(),
                ]);
            }

            if (isset($hashesDelGrupo[$lectura['hash_sha256']])) {
                throw ValidationException::withMessages([
                    "archivos.{$indice}" => $archivo->getClientOriginalName()
                        .': este mismo archivo fue seleccionado más de una vez.',
                ]);
            }

            $yaExiste = LoteArchivo::query()
                ->where('lote_mensual_id', $lote->id)
                ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
                ->where('hash_sha256', $lectura['hash_sha256'])
                ->exists();

            if ($yaExiste) {
                throw ValidationException::withMessages([
                    "archivos.{$indice}" => $archivo->getClientOriginalName()
                        .': este archivo de Certificados de Aportes ya fue cargado en el lote.',
                ]);
            }

            $hashesDelGrupo[$lectura['hash_sha256']] = true;
            $lecturas[] = [
                'archivo' => $archivo,
                'datos' => $lectura,
            ];
        }

        $rutasGuardadas = [];

        try {
            DB::transaction(function () use (
                $request,
                $lote,
                $lecturas,
                &$rutasGuardadas
            ): void {
                LoteMensual::query()->lockForUpdate()->findOrFail($lote->id);

                $cantidadActual = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
                    ->where('ruta', 'not like', '%/otros/%')
                    ->count();
                $cantidadFinal = $cantidadActual + count($lecturas);

                if ($cantidadFinal < 3 || $cantidadFinal > 10) {
                    throw ValidationException::withMessages([
                        'archivos' => 'El lote debe contener entre 3 y 10 archivos '
                            .'de Certificados de Aportes; '
                            ."con esta carga tendría {$cantidadFinal}.",
                    ]);
                }

                foreach ($lecturas as $item) {
                    /** @var UploadedFile $archivoSubido */
                    $archivoSubido = $item['archivo'];
                    $datos = $item['datos'];
                    $nombreGuardado = Str::uuid().'.'.$datos['extension'];
                    $directorio = "procesamiento-mensual/lotes/{$lote->id}/certificados";
                    $ruta = $archivoSubido->storeAs(
                        $directorio,
                        $nombreGuardado,
                        'local'
                    );

                    if (! $ruta) {
                        throw new \RuntimeException(
                            'No fue posible guardar uno de los archivos '
                            .'de Certificados de Aportes.'
                        );
                    }

                    $rutasGuardadas[] = $ruta;

                    $archivoLote = LoteArchivo::create([
                        'lote_mensual_id' => $lote->id,
                        'tipo' => LoteArchivo::TIPO_CERTIFICADOS,
                        'nombre_original' => $datos['nombre_original'],
                        'ruta' => $ruta,
                        'extension' => $datos['extension'],
                        'mime_type' => $datos['mime_type'],
                        'hash_sha256' => $datos['hash_sha256'],
                        'filas_importadas' => $datos['filas_importadas'],
                        'total_monto_descuento' => $datos['total_monto_descuento'],
                        'total_tot_2' => $datos['total_descuento'],
                        'total_comision' => $datos['total_tasa_regulacion'],
                        'estado' => LoteArchivo::ESTADO_CARGADO,
                        'cargado_por' => $request->user()?->id,
                    ]);

                    $ahora = now();
                    $registros = array_map(
                        fn (array $registro): array => [
                            ...$registro,
                            'lote_mensual_id' => $lote->id,
                            'lote_archivo_id' => $archivoLote->id,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ],
                        $datos['registros']
                    );

                    foreach (array_chunk($registros, 500) as $bloque) {
                        LoteCertificadoAporteRegistro::insert($bloque);
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($rutasGuardadas as $ruta) {
                Storage::disk('local')->delete($ruta);
            }

            if ($exception instanceof QueryException
                && str_contains($exception->getMessage(), 'lote_archivos_lote_tipo_hash_unique')) {
                throw ValidationException::withMessages([
                    'archivos' => 'Uno de los archivos de Certificados de Aportes '
                        .'ya fue cargado en este lote.',
                ]);
            }

            throw $exception;
        }

        $filas = array_sum(array_column(
            array_column($lecturas, 'datos'),
            'filas_importadas'
        ));

        $estadoLote->sincronizar($lote->fresh());

        return redirect()
            ->route('procesamiento-mensual.lotes.certificados.index', $lote)
            ->with(
                'success',
                count($lecturas).' archivo(s) de Certificados de Aportes cargado(s). '
                ."{$filas} fila(s) fueron incorporadas a la tabla consolidada."
            );
    }

    public function limpiar(
        LoteMensual $lote,
        EstadoLoteMensualService $estadoLote
    ): RedirectResponse {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return redirect()
                ->route('procesamiento-mensual.lotes.certificados.index', $lote)
                ->with(
                    'error',
                    'No es posible limpiar Certificados de Aportes porque el lote '
                    ."se encuentra {$lote->estado}."
                );
        }

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
            ->where('ruta', 'not like', '%/otros/%')
            ->get(['id', 'ruta']);

        if ($archivos->isEmpty()) {
            return redirect()
                ->route('procesamiento-mensual.lotes.certificados.index', $lote)
                ->with(
                    'info',
                    'No existen archivos de Certificados de Aportes para limpiar.'
                );
        }

        $idsArchivos = $archivos->pluck('id');
        $rutas = $archivos->pluck('ruta')->filter()->values()->all();

        DB::transaction(function () use ($lote, $idsArchivos): void {
            LoteCertificadoAporteRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->whereIn('lote_archivo_id', $idsArchivos)
                ->delete();

            LoteArchivo::query()
                ->where('lote_mensual_id', $lote->id)
                ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
                ->where('ruta', 'not like', '%/otros/%')
                ->whereIn('id', $idsArchivos)
                ->delete();
        });

        if ($rutas !== []) {
            Storage::disk('local')->delete($rutas);
        }

        $estadoLote->sincronizar($lote->fresh());

        return redirect()
            ->route('procesamiento-mensual.lotes.certificados.index', $lote)
            ->with(
                'success',
                'La importación de Certificados de Aportes fue limpiada. '
                .'Ya puede cargar un nuevo grupo.'
            );
    }
}
