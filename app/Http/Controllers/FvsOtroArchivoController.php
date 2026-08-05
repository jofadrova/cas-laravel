<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFvsOtroArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteFvsRegistro;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\FvsExcelImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class FvsOtroArchivoController extends Controller
{
    public function previsualizar(
        StoreFvsOtroArchivoRequest $request,
        LoteMensual $lote,
        FvsExcelImportService $importador
    ): JsonResponse {
        $this->validarNoFinalizado($lote);
        $datos = $this->leer($request, $lote, $importador);
        $this->validarHash($lote, $datos['hash_sha256']);
        [$nuevos, $duplicados] = $this->separarNuevos($lote, collect($datos['registros']));

        if ($nuevos->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => 'Todos los asociados con FVS ya se encuentran en los archivos del lote.',
            ]);
        }

        return response()->json([
            'hash' => $datos['hash_sha256'],
            'nombre' => $datos['nombre_original'],
            'filas' => $nuevos->count(),
            'omitidas_sin_fvs' => $datos['filas_omitidas_sin_importe'],
            'duplicadas' => $duplicados->pluck('codigo_personal')->values(),
            'total_fvs' => round($nuevos->sum('monto_descuento'), 2),
            'registros' => $nuevos->map(fn (array $registro): array => [
                'fila' => $registro['fila_origen'],
                'papeleta' => $registro['codigo_personal'],
                'carnet' => $registro['carnet'],
                'grado' => $registro['grado'],
                'nombres' => $registro['nombres'],
                'destino' => $registro['organismos'],
                'fvs' => round((float) $registro['monto_descuento'], 2),
            ])->values(),
        ]);
    }

    public function store(
        StoreFvsOtroArchivoRequest $request,
        LoteMensual $lote,
        FvsExcelImportService $importador
    ): RedirectResponse {
        $this->validarNoFinalizado($lote);
        $request->validate([
            'hash_preview' => ['required', 'string', 'size:64'],
        ], [
            'hash_preview.required' => 'Primero debe previsualizar la planilla.',
            'hash_preview.size' => 'La vista previa dejó de ser válida. Vuelva a generarla.',
        ]);

        $datos = $this->leer($request, $lote, $importador);

        if (! hash_equals($request->string('hash_preview')->toString(), $datos['hash_sha256'])) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo cambió después de la vista previa. Vuelva a previsualizarlo.',
            ]);
        }

        $ruta = null;

        try {
            DB::transaction(function () use ($request, $lote, $datos, &$ruta): void {
                LoteMensual::query()->whereKey($lote->id)->lockForUpdate()->firstOrFail();
                $this->validarNoFinalizado($lote);

                $principales = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_FVS)
                    ->where('ruta', 'not like', '%/otros/%')
                    ->lockForUpdate()
                    ->count();

                if ($principales < 3) {
                    throw ValidationException::withMessages([
                        'archivo' => 'Primero debe cargar el grupo principal de al menos 3 archivos FVS.',
                    ]);
                }

                $this->validarHash($lote, $datos['hash_sha256']);
                [$nuevos] = $this->separarNuevos($lote, collect($datos['registros']));

                if ($nuevos->isEmpty()) {
                    throw ValidationException::withMessages([
                        'archivo' => 'No quedan registros FVS nuevos para incorporar.',
                    ]);
                }

                $ruta = $request->file('archivo')->storeAs(
                    "procesamiento-mensual/lotes/{$lote->id}/fvs/otros",
                    Str::uuid().'.'.$datos['extension'],
                    'local'
                );

                if (! $ruta) {
                    throw new \RuntimeException('No fue posible guardar la planilla adicional FVS.');
                }

                $archivo = LoteArchivo::create([
                    'lote_mensual_id' => $lote->id,
                    'tipo' => LoteArchivo::TIPO_FVS,
                    'nombre_original' => $datos['nombre_original'],
                    'ruta' => $ruta,
                    'extension' => $datos['extension'],
                    'mime_type' => $datos['mime_type'],
                    'hash_sha256' => $datos['hash_sha256'],
                    'filas_importadas' => $nuevos->count(),
                    'total_monto_descuento' => round($nuevos->sum('monto_descuento'), 6),
                    'total_tot_2' => round($nuevos->sum('tot_2'), 6),
                    'total_comision' => 0,
                    'estado' => LoteArchivo::ESTADO_CARGADO,
                    'cargado_por' => $request->user()?->id,
                ]);

                $ahora = now();
                $filas = $nuevos->map(fn (array $registro): array => [
                    ...$registro,
                    'lote_mensual_id' => $lote->id,
                    'lote_archivo_id' => $archivo->id,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ])->all();

                foreach (array_chunk($filas, 500) as $bloque) {
                    LoteFvsRegistro::insert($bloque);
                }
            });
        } catch (Throwable $exception) {
            if ($ruta) {
                Storage::disk('local')->delete($ruta);
            }

            if ($exception instanceof QueryException) {
                throw ValidationException::withMessages([
                    'archivo' => 'La planilla ya fue cargada o contiene datos duplicados.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('procesamiento-mensual.lotes.fvs.index', $lote)
            ->with('success', 'La planilla adicional FVS fue incorporada correctamente.');
    }

    public function limpiar(LoteMensual $lote): RedirectResponse
    {
        if ($this->estaFvsFinalizado($lote)) {
            return back()->with(
                'error',
                'FVS ya fue finalizado; los archivos adicionales no pueden eliminarse.'
            );
        }

        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return back()->with('error', "El lote se encuentra {$lote->estado} y no admite cambios.");
        }

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_FVS)
            ->where('ruta', 'like', '%/otros/%')
            ->get(['id', 'ruta']);

        if ($archivos->isEmpty()) {
            return back()->with('info', 'No existen otros archivos FVS para limpiar.');
        }

        DB::transaction(function () use ($lote, $archivos): void {
            LoteFvsRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->whereIn('lote_archivo_id', $archivos->pluck('id'))
                ->delete();
            LoteArchivo::query()->whereIn('id', $archivos->pluck('id'))->delete();
        });

        Storage::disk('local')->delete($archivos->pluck('ruta')->filter()->all());

        return back()->with('success', 'Los otros archivos FVS fueron eliminados.');
    }

    private function leer(
        StoreFvsOtroArchivoRequest $request,
        LoteMensual $lote,
        FvsExcelImportService $importador
    ): array {
        try {
            return $importador->leerAdicional($request->file('archivo'), $lote);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['archivo' => $exception->getMessage()]);
        }
    }

    private function separarNuevos(LoteMensual $lote, Collection $registros): array
    {
        $existentes = LoteFvsRegistro::query()
            ->where('lote_mensual_id', $lote->id)
            ->pluck('codigo_personal')
            ->map(fn ($codigo): string => $this->normalizarCodigo($codigo))
            ->filter()->flip();

        return [
            $registros->reject(fn (array $fila): bool => $existentes->has(
                $this->normalizarCodigo($fila['codigo_personal'])
            ))->values(),
            $registros->filter(fn (array $fila): bool => $existentes->has(
                $this->normalizarCodigo($fila['codigo_personal'])
            ))->values(),
        ];
    }

    private function validarHash(LoteMensual $lote, string $hash): void
    {
        if (LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_FVS)
            ->where('hash_sha256', $hash)
            ->exists()) {
            throw ValidationException::withMessages([
                'archivo' => 'Esta misma planilla ya fue cargada en el lote.',
            ]);
        }
    }

    private function normalizarCodigo(mixed $codigo): string
    {
        $valor = trim((string) $codigo);

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor) ?? $valor;
            $valor = ltrim($valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }

    private function validarNoFinalizado(LoteMensual $lote): void
    {
        if ($this->estaFvsFinalizado($lote)) {
            throw ValidationException::withMessages([
                'archivo' => 'FVS ya fue finalizado y está pendiente para Contabilidad.',
            ]);
        }
    }

    private function estaFvsFinalizado(LoteMensual $lote): bool
    {
        return DB::table('lote_fvs_procesamientos')
            ->where('lote_mensual_id', $lote->id)
            ->exists();
    }
}
