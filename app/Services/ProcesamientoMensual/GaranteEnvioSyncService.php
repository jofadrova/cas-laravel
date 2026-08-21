<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteArchivo;
use App\Models\LoteGaranteRegistro;
use App\Models\LoteMensual;
use App\Models\LotePrestamoConciliacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use LogicException;

class GaranteEnvioSyncService
{
    public function __construct(
        private readonly GaranteExcelImportService $importador
    ) {}

    public function sincronizar(LoteMensual $lote, ?int $usuarioId): int
    {
        $envio = $lote->envioMensual()
            ->with('archivoGarantes')
            ->first();
        $origen = $envio?->archivoGarantes;

        if (! $origen) {
            throw new LogicException(
                'El envío mensual no contiene el Excel obligatorio de garantes.'
            );
        }

        if (! Storage::disk('local')->exists($origen->ruta)) {
            throw new LogicException(
                'El Excel de garantes guardado en el envío no está disponible.'
            );
        }

        $rutaAbsoluta = Storage::disk('local')->path($origen->ruta);

        if (hash_file('sha256', $rutaAbsoluta) !== $origen->hash_sha256) {
            throw new LogicException(
                'El Excel de garantes del envío no supera la verificación de integridad.'
            );
        }

        $extension = strtolower(pathinfo(
            $origen->nombre_original,
            PATHINFO_EXTENSION
        )) ?: 'xlsx';
        $archivo = new UploadedFile(
            $rutaAbsoluta,
            $origen->nombre_original,
            $origen->mime_type,
            null,
            true
        );

        try {
            $datos = $this->importador->leer($archivo, $lote);
        } catch (InvalidArgumentException $exception) {
            throw new LogicException(
                'No fue posible utilizar el Excel de garantes del envío: '
                .$exception->getMessage(),
                previous: $exception
            );
        }

        DB::transaction(function () use (
            $lote,
            $origen,
            $datos,
            $extension,
            $usuarioId
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

            if (LoteGaranteRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->where(
                    'estado_aplicacion',
                    LoteGaranteRegistro::APLICACION_APLICADO
                )
                ->exists()) {
                throw new LogicException(
                    'Existen descuentos a garantes ya aplicados y no pueden recalcularse.'
                );
            }

            LotePrestamoConciliacion::query()
                ->where('lote_mensual_id', $lote->id)
                ->delete();
            LoteGaranteRegistro::query()
                ->where('lote_mensual_id', $lote->id)
                ->delete();
            LoteArchivo::query()
                ->where('lote_mensual_id', $lote->id)
                ->where('tipo', LoteArchivo::TIPO_GARANTES)
                ->delete();

            $archivoLote = LoteArchivo::create([
                'lote_mensual_id' => $lote->id,
                'tipo' => LoteArchivo::TIPO_GARANTES,
                'nombre_original' => $origen->nombre_original,
                'ruta' => $origen->ruta,
                'extension' => $extension,
                'mime_type' => $origen->mime_type,
                'hash_sha256' => $origen->hash_sha256,
                'filas_importadas' => $datos['filas_importadas'],
                'total_monto_descuento' => $datos['total_monto_descuento'],
                'total_tot_2' => 0,
                'total_comision' => 0,
                'estado' => LoteArchivo::ESTADO_CARGADO,
                'cargado_por' => $usuarioId,
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
        });

        return (int) $datos['filas_importadas'];
    }
}
