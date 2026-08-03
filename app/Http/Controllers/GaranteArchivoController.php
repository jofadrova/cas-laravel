<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGaranteArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use App\Models\LotePrestamoConciliacion;
use App\Services\ProcesamientoMensual\GaranteExcelImportService;
use App\Services\ProcesamientoMensual\PrestamoConciliacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;
use Throwable;

class GaranteArchivoController extends Controller
{
    public function store(
        StoreGaranteArchivoRequest $request,
        LoteMensual $lote,
        GaranteExcelImportService $importador,
        PrestamoConciliacionService $conciliador
    ): RedirectResponse {
        /** @var array<int, UploadedFile> $archivos */
        $archivos = $request->file('archivos_garantes', []);
        $lecturas = [];
        $hashesDelGrupo = [];

        foreach ($archivos as $indice => $archivo) {
            try {
                $lectura = $importador->leer($archivo, $lote);
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    "archivos_garantes.{$indice}" => $archivo->getClientOriginalName()
                        .': '
                        .$exception->getMessage(),
                ]);
            }

            if (isset($hashesDelGrupo[$lectura['hash_sha256']])) {
                throw ValidationException::withMessages([
                    "archivos_garantes.{$indice}" => $archivo->getClientOriginalName()
                        .': este archivo fue seleccionado más de una vez.',
                ]);
            }

            $hashesDelGrupo[$lectura['hash_sha256']] = true;
            $lecturas[] = [
                'archivo' => $archivo,
                'datos' => $lectura,
            ];
        }

        $tieneAplicados = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where(
                'estado_aplicacion',
                LoteGaranteRegistro::APLICACION_APLICADO
            )
            ->exists();

        if ($tieneAplicados) {
            throw ValidationException::withMessages([
                'archivos_garantes' => 'No se puede reemplazar el archivo porque el lote ya '
                    .'contiene descuentos a garantes aplicados.',
            ]);
        }

        $rutasGuardadas = [];
        $rutasAnteriores = [];

        try {
            DB::transaction(function () use (
                $request,
                $lote,
                $lecturas,
                $conciliador,
                &$rutasGuardadas,
                &$rutasAnteriores
            ): void {
                LoteMensual::query()
                    ->whereKey($lote->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (DB::table('lote_prestamo_procesamientos')
                    ->where('lote_mensual_id', $lote->id)
                    ->exists()) {
                    throw new LogicException(
                        'El pago mensual de Préstamos ya fue consolidado.'
                    );
                }

                $archivosAnteriores = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_GARANTES)
                    ->lockForUpdate()
                    ->get(['id', 'ruta']);

                $aplicadoBloqueado = LoteGaranteRegistro::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where(
                        'estado_aplicacion',
                        LoteGaranteRegistro::APLICACION_APLICADO
                    )
                    ->lockForUpdate()
                    ->first(['id']);

                if ($aplicadoBloqueado !== null) {
                    throw new LogicException(
                        'El lote ya contiene descuentos a garantes aplicados.'
                    );
                }

                $idsAnteriores = $archivosAnteriores->pluck('id');
                $rutasAnteriores = $archivosAnteriores
                    ->pluck('ruta')
                    ->filter()
                    ->values()
                    ->all();

                /*
                 * Las conciliaciones contienen referencias a los registros
                 * de garantes. Se eliminan primero y se reconstruyen al final
                 * con el archivo recién importado.
                 */
                LotePrestamoConciliacion::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->delete();

                LoteGaranteRegistro::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->delete();

                if ($idsAnteriores->isNotEmpty()) {
                    LoteArchivo::query()
                        ->where('lote_mensual_id', $lote->id)
                        ->whereIn('id', $idsAnteriores)
                        ->delete();
                }

                foreach ($lecturas as $item) {
                    /** @var UploadedFile $archivoSubido */
                    $archivoSubido = $item['archivo'];
                    $datos = $item['datos'];
                    $nombreGuardado = Str::uuid().'.'.$datos['extension'];
                    $directorio =
                        "procesamiento-mensual/lotes/{$lote->id}/garantes";
                    $ruta = $archivoSubido->storeAs(
                        $directorio,
                        $nombreGuardado,
                        'local'
                    );

                    if (! $ruta) {
                        throw new \RuntimeException(
                            'No fue posible guardar el archivo original de garantes.'
                        );
                    }

                    $rutasGuardadas[] = $ruta;
                    $archivoLote = LoteArchivo::create([
                        'lote_mensual_id' => $lote->id,
                        'tipo' => LoteArchivo::TIPO_GARANTES,
                        'nombre_original' => $datos['nombre_original'],
                        'ruta' => $ruta,
                        'extension' => $datos['extension'],
                        'mime_type' => $datos['mime_type'],
                        'hash_sha256' => $datos['hash_sha256'],
                        'filas_importadas' => $datos['filas_importadas'],
                        'total_monto_descuento' => $datos['total_monto_descuento'],
                        'total_tot_2' => 0,
                        'total_comision' => 0,
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
                        LoteGaranteRegistro::insert($bloque);
                    }
                }

                /*
                 * La carga y la comparación forman una sola operación.
                 * Si el recálculo falla, la transacción restaura los datos
                 * anteriores y no deja una importación a medio procesar.
                 */
                $conciliador->ejecutar($lote, $request->user()?->id);
            });
        } catch (Throwable $exception) {
            foreach ($rutasGuardadas as $ruta) {
                Storage::disk('local')->delete($ruta);
            }

            if ($exception instanceof LogicException) {
                throw ValidationException::withMessages([
                    'archivos_garantes' => 'El archivo fue validado, pero no pudo compararse: '
                        .$exception->getMessage(),
                ]);
            }

            throw $exception;
        }

        if ($rutasAnteriores !== []) {
            Storage::disk('local')->delete($rutasAnteriores);
        }

        $filas = array_sum(array_column(
            array_column($lecturas, 'datos'),
            'filas_importadas'
        ));

        return redirect()
            ->route('procesamiento-mensual.lotes.archivos.index', $lote)
            ->with(
                'success',
                count($lecturas)
                .' archivo(s) de descuentos a garantes cargado(s) y '
                ."comparado(s) automáticamente. {$filas} descuento(s) "
                .'fueron recalculados.'
            );
    }

    public function limpiar(LoteMensual $lote): RedirectResponse
    {
        if (DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists()) {
            return back()->with(
                'error',
                'No se puede limpiar garantes porque el pago mensual de '
                .'Préstamos ya fue consolidado.'
            );
        }

        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return back()->with(
                'error',
                "No se puede limpiar garantes porque el lote está {$lote->estado}."
            );
        }

        $tieneAplicados = LoteGaranteRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('estado_aplicacion', LoteGaranteRegistro::APLICACION_APLICADO)
            ->exists();

        if ($tieneAplicados) {
            return back()->with(
                'error',
                'No se puede limpiar el archivo porque ya existen descuentos aplicados.'
            );
        }

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_GARANTES)
            ->get(['id', 'ruta']);

        if ($archivos->isEmpty()) {
            return back()->with(
                'info',
                'No existen archivos de descuentos a garantes para limpiar.'
            );
        }

        $idsArchivos = $archivos->pluck('id');
        $rutas = $archivos->pluck('ruta')->filter()->values()->all();

        DB::transaction(function () use ($lote, $idsArchivos): void {
            LotePrestamoConciliacion::query()
                ->where('lote_mensual_id', $lote->id)
                ->delete();

            LoteGaranteRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->whereIn('lote_archivo_id', $idsArchivos)
                ->delete();

            LoteArchivo::query()
                ->where('lote_mensual_id', $lote->id)
                ->whereIn('id', $idsArchivos)
                ->delete();
        });

        if ($rutas !== []) {
            Storage::disk('local')->delete($rutas);
        }

        return back()->with(
            'success',
            'La importación de descuentos a garantes fue eliminada.'
        );
    }
}
