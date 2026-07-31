<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrestamoArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use App\Models\LotePrestamoRegistro;
use App\Services\ProcesamientoMensual\EstadoLoteMensualService;
use App\Services\ProcesamientoMensual\PrestamoExcelImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class PrestamoArchivoController extends Controller
{
    public function store(
        StorePrestamoArchivoRequest $request,
        LoteMensual $lote,
        PrestamoExcelImportService $importador,
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
                        . ': ' . $exception->getMessage(),
                ]);
            }

            if (isset($hashesDelGrupo[$lectura['hash_sha256']])) {
                throw ValidationException::withMessages([
                    "archivos.{$indice}" => $archivo->getClientOriginalName()
                        . ': este mismo archivo fue seleccionado más de una vez.',
                ]);
            }

            $yaExiste = LoteArchivo::query()
                ->where('lote_mensual_id', $lote->id)
                ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
                ->where('hash_sha256', $lectura['hash_sha256'])
                ->exists();

            if ($yaExiste) {
                throw ValidationException::withMessages([
                    "archivos.{$indice}" => $archivo->getClientOriginalName()
                        . ': este archivo ya fue cargado anteriormente en el lote.',
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
                LoteMensual::query()
                    ->whereKey($lote->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (DB::table('lote_prestamo_procesamientos')
                    ->where('lote_mensual_id', $lote->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'archivos' => 'El pago mensual de Préstamos ya fue '
                            . 'consolidado. No se admiten nuevas cargas.',
                    ]);
                }

                foreach ($lecturas as $item) {
                    /** @var UploadedFile $archivoSubido */
                    $archivoSubido = $item['archivo'];
                    $datos = $item['datos'];
                    $nombreGuardado = Str::uuid() . '.' . $datos['extension'];
                    $directorio = "procesamiento-mensual/lotes/{$lote->id}/prestamos";
                    $ruta = $archivoSubido->storeAs(
                        $directorio,
                        $nombreGuardado,
                        'local'
                    );

                    if (! $ruta) {
                        throw new \RuntimeException(
                            'No fue posible guardar uno de los archivos originales.'
                        );
                    }

                    $rutasGuardadas[] = $ruta;

                    $archivoLote = LoteArchivo::create([
                        'lote_mensual_id' => $lote->id,
                        'tipo' => LoteArchivo::TIPO_PRESTAMOS,
                        'nombre_original' => $datos['nombre_original'],
                        'ruta' => $ruta,
                        'extension' => $datos['extension'],
                        'mime_type' => $datos['mime_type'],
                        'hash_sha256' => $datos['hash_sha256'],
                        'filas_importadas' => $datos['filas_importadas'],
                        'total_monto_descuento' => $datos['total_monto_descuento'],
                        'total_tot_2' => $datos['total_tot_2'],
                        'total_comision' => $datos['total_comision'],
                        'estado' => LoteArchivo::ESTADO_CARGADO,
                        'cargado_por' => $request->user()?->id,
                    ]);

                    $ahora = now();
                    $registros = array_map(
                        function (array $registro) use (
                            $lote,
                            $archivoLote,
                            $ahora
                        ): array {
                            return [
                                ...$registro,
                                'lote_mensual_id' => $lote->id,
                                'lote_archivo_id' => $archivoLote->id,
                                'created_at' => $ahora,
                                'updated_at' => $ahora,
                            ];
                        },
                        $datos['registros']
                    );

                    foreach (array_chunk($registros, 500) as $bloque) {
                        LotePrestamoRegistro::insert($bloque);
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
                    'archivos' => 'Uno de los archivos ya fue cargado en este lote.',
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
            ->route('procesamiento-mensual.lotes.archivos.index', $lote)
            ->with(
                'success',
                count($lecturas) . ' archivo(s) de préstamos cargado(s). '
                . "{$filas} fila(s) fueron incorporadas a la tabla consolidada."
            );
    }

    public function limpiar(
        LoteMensual $lote,
        EstadoLoteMensualService $estadoLote
    ): RedirectResponse
    {
        if (DB::table('lote_prestamo_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists()) {
            return redirect()
                ->route('procesamiento-mensual.lotes.archivos.index', $lote)
                ->with(
                    'error',
                    'No es posible limpiar Préstamos porque el pago mensual '
                    . 'ya fue consolidado. El grupo permanece solo para consulta.'
                );
        }

        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return redirect()
                ->route('procesamiento-mensual.lotes.archivos.index', $lote)
                ->with(
                    'error',
                    "No es posible limpiar la importación porque el lote se encuentra {$lote->estado}."
                );
        }

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
            ->get(['id', 'ruta']);

        if ($archivos->isEmpty()) {
            return redirect()
                ->route('procesamiento-mensual.lotes.archivos.index', $lote)
                ->with('info', 'No existen archivos de préstamos para limpiar.');
        }

        $idsArchivos = $archivos->pluck('id');
        $rutas = $archivos->pluck('ruta')->filter()->values()->all();

        DB::transaction(function () use ($lote, $idsArchivos): void {
            LotePrestamoRegistro::query()
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

        $estadoLote->sincronizar($lote->fresh());

        return redirect()
            ->route('procesamiento-mensual.lotes.archivos.index', $lote)
            ->with(
                'success',
                'La importación de préstamos fue limpiada. Ya puede cargar otros archivos.'
            );
    }
}
