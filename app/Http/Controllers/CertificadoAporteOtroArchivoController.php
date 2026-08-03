<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificadoAporteOtroArchivoRequest;
use App\Models\LoteArchivo;
use App\Models\LoteCertificadoAporteRegistro;
use App\Models\LoteMensual;
use App\Services\ProcesamientoMensual\CertificadoAporteExcelImportService;
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

class CertificadoAporteOtroArchivoController extends Controller
{
    public function previsualizar(
        StoreCertificadoAporteOtroArchivoRequest $request,
        LoteMensual $lote,
        CertificadoAporteExcelImportService $importador
    ): JsonResponse {
        $datos = $this->leer($request, $lote, $importador);
        $this->validarHash($lote, $datos['hash_sha256']);
        [$nuevos, $duplicados] = $this->separarNuevos($lote, collect($datos['registros']));

        if ($nuevos->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => 'Todos los asociados con APORTE ya se encuentran en los archivos del lote.',
            ]);
        }

        return response()->json([
            'hash' => $datos['hash_sha256'],
            'nombre' => $datos['nombre_original'],
            'filas' => $nuevos->count(),
            'omitidas_sin_aporte' => $datos['filas_omitidas_sin_importe'],
            'duplicadas' => $duplicados->pluck('codigo_personal')->values(),
            'total_aporte' => round($nuevos->sum('monto_descuento'), 2),
            'registros' => $nuevos->map(fn (array $registro): array => [
                'fila' => $registro['fila_origen'],
                'papeleta' => $registro['codigo_personal'],
                'carnet' => $registro['carnet'],
                'grado' => $registro['grado'],
                'nombres' => $registro['nombres'],
                'destino' => $registro['organismos'],
                'aporte' => round((float) $registro['monto_descuento'], 2),
            ])->values(),
        ]);
    }

    public function store(
        StoreCertificadoAporteOtroArchivoRequest $request,
        LoteMensual $lote,
        CertificadoAporteExcelImportService $importador
    ): RedirectResponse {
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

                $principales = LoteArchivo::query()
                    ->where('lote_mensual_id', $lote->id)
                    ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
                    ->where('ruta', 'not like', '%/otros/%')
                    ->lockForUpdate()
                    ->count();

                if ($principales < 3) {
                    throw ValidationException::withMessages([
                        'archivo' => 'Primero debe cargar al menos 3 archivos principales '
                            .'de Certificados de Aportes.',
                    ]);
                }

                $this->validarHash($lote, $datos['hash_sha256']);
                [$nuevos] = $this->separarNuevos($lote, collect($datos['registros']));

                if ($nuevos->isEmpty()) {
                    throw ValidationException::withMessages([
                        'archivo' => 'No quedan registros de aportes nuevos para incorporar.',
                    ]);
                }

                $ruta = $request->file('archivo')->storeAs(
                    "procesamiento-mensual/lotes/{$lote->id}/certificados/otros",
                    Str::uuid().'.'.$datos['extension'],
                    'local'
                );

                if (! $ruta) {
                    throw new \RuntimeException('No fue posible guardar la planilla adicional de aportes.');
                }

                $archivo = LoteArchivo::create([
                    'lote_mensual_id' => $lote->id,
                    'tipo' => LoteArchivo::TIPO_CERTIFICADOS,
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
                    LoteCertificadoAporteRegistro::insert($bloque);
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
            ->route('procesamiento-mensual.lotes.certificados.index', $lote)
            ->with('success', 'La planilla adicional de aportes fue incorporada correctamente.');
    }

    public function limpiar(LoteMensual $lote): RedirectResponse
    {
        if (in_array($lote->estado, [
            LoteMensual::ESTADO_PROCESADO,
            LoteMensual::ESTADO_CERRADO,
            LoteMensual::ESTADO_ANULADO,
        ], true)) {
            return back()->with('error', "El lote se encuentra {$lote->estado} y no admite cambios.");
        }

        $archivos = LoteArchivo::query()
            ->where('lote_mensual_id', $lote->id)
            ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
            ->where('ruta', 'like', '%/otros/%')
            ->get(['id', 'ruta']);

        if ($archivos->isEmpty()) {
            return back()->with('info', 'No existen otros archivos de aportes para limpiar.');
        }

        DB::transaction(function () use ($lote, $archivos): void {
            LoteCertificadoAporteRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->whereIn('lote_archivo_id', $archivos->pluck('id'))
                ->delete();
            LoteArchivo::query()->whereIn('id', $archivos->pluck('id'))->delete();
        });

        Storage::disk('local')->delete($archivos->pluck('ruta')->filter()->all());

        return back()->with('success', 'Los otros archivos de aportes fueron eliminados.');
    }

    private function leer(
        StoreCertificadoAporteOtroArchivoRequest $request,
        LoteMensual $lote,
        CertificadoAporteExcelImportService $importador
    ): array {
        try {
            return $importador->leerAdicional($request->file('archivo'), $lote);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['archivo' => $exception->getMessage()]);
        }
    }

    private function separarNuevos(LoteMensual $lote, Collection $registros): array
    {
        $existentes = LoteCertificadoAporteRegistro::query()
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
            ->where('tipo', LoteArchivo::TIPO_CERTIFICADOS)
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
}
