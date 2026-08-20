<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\LoteCertificadoAporteRegistro;
use App\Models\LoteCertificadoAporteSeparacion;
use App\Models\LoteMensual;
use Illuminate\Support\Facades\DB;
use LogicException;

class CertificadoAporteConsolidacionService
{
    public const ESTADO_CONTABLE_PENDIENTE = 'PENDIENTE';

    public function __construct(
        private readonly EstadoLoteMensualService $estadoLote
    ) {}

    public function ejecutar(LoteMensual $lote, ?int $usuarioId): object
    {
        return DB::transaction(function () use ($lote, $usuarioId): object {
            $loteBloqueado = LoteMensual::query()
                ->whereKey($lote->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($loteBloqueado->estado, [
                LoteMensual::ESTADO_PROCESADO,
                LoteMensual::ESTADO_CERRADO,
                LoteMensual::ESTADO_ANULADO,
            ], true)) {
                throw new LogicException(
                    "El lote se encuentra {$loteBloqueado->estado} y no admite esta operación."
                );
            }

            if ($this->estaConsolidado($loteBloqueado->id)) {
                throw new LogicException(
                    'Los Certificados de Aportes ya fueron consolidados y están pendientes para Contabilidad.'
                );
            }

            $registros = LoteCertificadoAporteRegistro::query()
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->selectRaw('COUNT(*) AS cantidad')
                ->selectRaw('COALESCE(SUM(monto_descuento), 0) AS monto_descuento')
                ->selectRaw('COALESCE(SUM(tasa_regulacion), 0) AS tasa_regulacion')
                ->selectRaw('COALESCE(SUM(total_descuento), 0) AS total_descuento')
                ->first();

            if ((int) $registros->cantidad === 0) {
                throw new LogicException('No existen Certificados de Aportes para consolidar.');
            }

            $separacion = LoteCertificadoAporteSeparacion::query()
                ->where('lote_mensual_id', $loteBloqueado->id)
                ->selectRaw('COUNT(*) AS cantidad')
                ->selectRaw('COALESCE(SUM(monto_total), 0) AS monto_total')
                ->selectRaw('COALESCE(SUM(monto_ao), 0) AS monto_ao')
                ->selectRaw('COALESCE(SUM(monto_av), 0) AS monto_av')
                ->selectRaw('COALESCE(SUM(monto_ai), 0) AS monto_ai')
                ->first();

            if ((int) $separacion->cantidad !== (int) $registros->cantidad) {
                throw new LogicException(
                    'La separación AO, AV y AI está incompleta. Vuelva a separar antes de consolidar.'
                );
            }

            $totalCentavos = $this->aCentavos($registros->total_descuento);
            $separadoCentavos = $this->aCentavos($separacion->monto_total);
            $componentesCentavos = $this->aCentavos($separacion->monto_ao)
                + $this->aCentavos($separacion->monto_av)
                + $this->aCentavos($separacion->monto_ai);

            if ($totalCentavos !== $separadoCentavos
                || $separadoCentavos !== $componentesCentavos) {
                throw new LogicException(
                    'Los totales AO, AV y AI no coinciden con TOTAL_DESCUENTO. Vuelva a separar.'
                );
            }

            $ahora = now();
            $id = DB::table('lote_certificado_aporte_procesamientos')->insertGetId([
                'lote_mensual_id' => $loteBloqueado->id,
                'cantidad_registros' => (int) $registros->cantidad,
                'monto_descuento' => $this->aMonto($registros->monto_descuento),
                'tasa_regulacion' => $this->aMonto($registros->tasa_regulacion),
                'total_descuento' => $this->aMonto($registros->total_descuento),
                'monto_ao' => $this->aMonto($separacion->monto_ao),
                'monto_av' => $this->aMonto($separacion->monto_av),
                'monto_ai' => $this->aMonto($separacion->monto_ai),
                'estado_contable' => self::ESTADO_CONTABLE_PENDIENTE,
                'asiento_contable_id' => null,
                'consolidado_por' => $usuarioId,
                'fecha_consolidacion' => $ahora,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $this->estadoLote->sincronizar($loteBloqueado);

            return DB::table('lote_certificado_aporte_procesamientos')->find($id);
        });
    }

    public function estaConsolidado(int $loteId): bool
    {
        return DB::table('lote_certificado_aporte_procesamientos')
            ->where('lote_mensual_id', $loteId)
            ->exists();
    }

    private function aCentavos(mixed $monto): int
    {
        return (int) round((float) $monto * 100, 0, PHP_ROUND_HALF_UP);
    }

    private function aMonto(mixed $monto): float
    {
        return $this->aCentavos($monto) / 100;
    }
}
