<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrestamoOtroArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteMensual;
use App\Models\LotePrestamoRegistro;
use App\Services\ProcesamientoMensual\PrestamoConciliacionService;
use App\Services\ProcesamientoMensual\PrestamoOtroArchivoImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;
use Throwable;

class PrestamoOtroArchivoController extends Controller
{
    public function previsualizar(
        StorePrestamoOtroArchivoRequest $request,
        LoteMensual $lote,
        PrestamoOtroArchivoImportService $importador
    ): JsonResponse {
        try {
            $datos = $importador->leer($request->file('archivo'), $lote);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'archivo' => $exception->getMessage(),
            ]);
        }

        $this->validarHashNoCargado($lote, $datos['hash_sha256']);
        [$nuevos, $duplicados] = $this->separarRegistrosNuevos(
            $lote,
            collect($datos['registros'])
        );

        if ($nuevos->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => 'Todas las papeletas de la planilla ya se encuentran '
                    . 'en los archivos de Préstamos del lote.',
            ]);
        }

        return response()->json([
            'hash' => $datos['hash_sha256'],
            'nombre' => $datos['nombre_original'],
            'filas' => $nuevos->count(),
            'omitidas_sin_prestamo' => $datos['filas_omitidas_sin_prestamo'],
            'duplicadas' => $duplicados->pluck('codigo_personal')->values(),
            'total_prestamo' => round($nuevos->sum('tot_2'), 2),
            'total_comision' => round($nuevos->sum('comision'), 2),
            'total_aplicado' => round($nuevos->sum('monto_descuento'), 2),
            'registros' => $nuevos->map(fn (array $registro): array => [
                'fila' => $registro['fila_origen'],
                'papeleta' => $registro['codigo_personal'],
                'carnet' => $registro['carnet'],
                'grado' => $registro['grado'],
                'nombres' => $registro['nombres'],
                'destino' => $registro['organismos'],
                'prestamo' => round((float) $registro['tot_2'], 2),
                'ser_adm' => round((float) $registro['comision'], 2),
                'total_aplicado' => round(
                    (float) $registro['monto_descuento'],
                    2
                ),
            ])->values(),
        ]);
    }

    public function store(
        StorePrestamoOtroArchivoRequest $request,
        LoteMensual $lote,
        PrestamoOtroArchivoImportService $importador,
        PrestamoConciliacionService $conciliador
    ): RedirectResponse {
        $request->validate([
            'hash_preview' => ['required', 'string', 'size:64'],
        ], [
            'hash_preview.required' => 'Primero debe previsualizar la planilla.',
            'hash_preview.size' => 'La vista previa dejó de ser válida. Vuelva a generarla.',
        ]);

        try {
            $datos = $importador->leer($request->file('archivo'), $lote);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'archivo' => $exception->getMessage(),
            ]);
        }

        if (! hash_equals(
            $request->string('hash_preview')->toString(),
            $datos['hash_sha256']
        )) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo cambió después de la vista previa. '
                    . 'Vuelva a previsualizarlo antes de confirmar.',
            ]);
        }

        $rutaGuardada = null;

        try {
            DB::transaction(function () use (
                $request,
                $lote,
                $datos,
                $conciliador,
                &$rutaGuardada
            ): void {
                $loteBloqueado = LoteMensual::query()
                    ->whereKey($lote->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (DB::table('lote_prestamo_procesamientos')
                    ->where('lote_mensual_id', $lote->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'archivo' => 'El pago mensual ya fue consolidado.',
                    ]);
                }

                $this->validarHashNoCargado($lote, $datos['hash_sha256']);
                [$nuevos] = $this->separarRegistrosNuevos(
                    $lote,
                    collect($datos['registros'])
                );

                if ($nuevos->isEmpty()) {
                    throw ValidationException::withMessages([
                        'archivo' => 'No quedan registros nuevos para incorporar.',
                    ]);
                }

                $nombreGuardado = Str::uuid() . '.' . $datos['extension'];
                $directorio = "procesamiento-mensual/lotes/{$lote->id}/prestamos/otros";
                $rutaGuardada = $request->file('archivo')->storeAs(
                    $directorio,
                    $nombreGuardado,
                    'local'
                );

                if (! $rutaGuardada) {
                    throw new \RuntimeException(
                        'No fue posible guardar la planilla adicional original.'
                    );
                }

                $archivoLote = LoteArchivo::create([
                    'lote_mensual_id' => $lote->id,
                    'tipo' => LoteArchivo::TIPO_PRESTAMOS,
                    'nombre_original' => $datos['nombre_original'],
                    'ruta' => $rutaGuardada,
                    'extension' => $datos['extension'],
                    'mime_type' => $datos['mime_type'],
                    'hash_sha256' => $datos['hash_sha256'],
                    'filas_importadas' => $nuevos->count(),
                    'total_monto_descuento' => round($nuevos->sum('monto_descuento'), 6),
                    'total_tot_2' => round($nuevos->sum('tot_2'), 6),
                    'total_comision' => round($nuevos->sum('comision'), 6),
                    'estado' => LoteArchivo::ESTADO_CARGADO,
                    'cargado_por' => $request->user()?->id,
                ]);

                $ahora = now();
                $filas = $nuevos->map(fn (array $registro): array => [
                    ...$registro,
                    'lote_mensual_id' => $lote->id,
                    'lote_archivo_id' => $archivoLote->id,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ])->all();

                foreach (array_chunk($filas, 500) as $bloque) {
                    LotePrestamoRegistro::insert($bloque);
                }

                $conciliador->ejecutar($loteBloqueado, $request->user()?->id);
            });
        } catch (Throwable $exception) {
            if ($rutaGuardada) {
                Storage::disk('local')->delete($rutaGuardada);
            }

            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages([
                    'archivo' => 'La planilla ya fue cargada o contiene datos duplicados.',
                ]);
            }

            if ($exception instanceof LogicException) {
                throw ValidationException::withMessages([
                    'archivo' => 'La planilla no pudo incorporarse a la comparación: '
                        . $exception->getMessage(),
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route(
                'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                $lote
            )
            ->with(
                'success',
                'La planilla adicional fue incorporada y la comparación '
                . 'de Préstamos se recalculó correctamente.'
            );
    }

    private function separarRegistrosNuevos(
        LoteMensual $lote,
        Collection $registros
    ): array {
        $existentes = LotePrestamoRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->pluck('codigo_personal')
            ->map(fn ($codigo): string => $this->normalizarCodigoPersonal($codigo))
            ->filter()
            ->flip();

        return [
            $registros->reject(
                fn (array $registro): bool => $existentes->has(
                    $this->normalizarCodigoPersonal($registro['codigo_personal'])
                )
            )->values(),
            $registros->filter(
                fn (array $registro): bool => $existentes->has(
                    $this->normalizarCodigoPersonal($registro['codigo_personal'])
                )
            )->values(),
        ];
    }

    private function validarHashNoCargado(
        LoteMensual $lote,
        string $hash
    ): void {
        if (LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_PRESTAMOS)
            ->where('hash_sha256', $hash)
            ->exists()) {
            throw ValidationException::withMessages([
                'archivo' => 'Esta misma planilla ya fue cargada en el lote.',
            ]);
        }
    }

    private function normalizarCodigoPersonal(mixed $codigo): string
    {
        $valor = trim((string) $codigo);

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
            $valor = ltrim($valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }
}
