<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StorePagoCuotaRequest;
use App\Http\Requests\StorePagoTotalRequest;
use App\Models\Pago;
use App\Models\PagoCuota;
use App\Models\Prestamo;
use Barryvdh\DomPDF\Facade\Pdf;

class PagoController extends Controller
{
    public function index(Prestamo $prestamo)
    {
        abort_if($prestamo->estado !== 'AC', 403, 'El préstamo cerrado no admite nuevos pagos.');

        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'cuotas' => fn ($query) => $query
                ->with('pagosCuotas.pago')
                ->orderBy('nro_cuota'),
        ]);

        $cuotasPagadas = $prestamo->cuotas
            ->where('estado', 'AC')
            ->count();

        $mesActual = now()->month;
        $gestionActual = now()->year;

        $cuotasPendientes = $prestamo->cuotas()
            ->where('estado', 'PE')
            ->where(function ($q) use ($mesActual, $gestionActual) {

                $q->where('gestion', '<', $gestionActual)

                ->orWhere(function ($q) use ($mesActual, $gestionActual) {

                    $q->where('gestion', $gestionActual)
                        ->where('mes', '<=', $mesActual);

                });

            })
            ->orderBy('gestion')
            ->orderBy('mes')
            ->orderBy('nro_cuota')
            ->get();

        $cantidadCuotasPendientes = $prestamo->cuotas()
            ->where('estado', 'PE')
            ->count();

        return view('pagos.index', compact(
            'prestamo',
            'cuotasPagadas',
            'cuotasPendientes',
            'cantidadCuotasPendientes'
        ));
    }

    public function store(StorePagoCuotaRequest $request, Prestamo $prestamo)
    {
        $datos = $request->validated();

        $prestamoCerrado = DB::transaction(function () use ($datos, $prestamo) {
            $prestamoBloqueado = Prestamo::query()
                ->with('tipo')
                ->whereKey($prestamo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($prestamoBloqueado->estado !== 'AC') {
                throw ValidationException::withMessages([
                    'cuotas' => 'El préstamo está cerrado y no admite nuevos pagos.',
                ]);
            }

            $cuotas = $prestamo->cuotas()
                ->whereIn('id', $datos['cuotas'])
                ->lockForUpdate()
                ->get();

            if ($cuotas->count() !== count($datos['cuotas'])) {
                throw ValidationException::withMessages([
                    'cuotas' => 'Una o más cuotas seleccionadas no pertenecen al préstamo.',
                ]);
            }

            if ($cuotas->contains(fn ($cuota) => $cuota->estado !== 'PE')) {
                throw ValidationException::withMessages([
                    'cuotas' => 'Una o más cuotas seleccionadas ya fueron pagadas.',
                ]);
            }

            $totalCuotas = round((float) $cuotas->sum('cuota_fija'), 2);
            $montoEfectivo = round((float) $datos['monto_efectivo'], 2);
            $esDolares = $prestamoBloqueado->tipo->tipo_moneda === 'SU';
            $tipoCambio = $esDolares ? (float) ($datos['tipo_cambio'] ?? 0) : 0;

            if ($esDolares && $tipoCambio <= 0) {
                throw ValidationException::withMessages([
                    'monto_efectivo' => 'El préstamo en dólares no tiene un tipo de cambio válido registrado.',
                ]);
            }

            $totalExigidoEnBolivianos = $esDolares
                ? round($totalCuotas * $tipoCambio, 2)
                : $totalCuotas;

            if ($montoEfectivo < $totalExigidoEnBolivianos) {
                throw ValidationException::withMessages([
                    'monto_efectivo' => $esDolares
                        ? 'El pago efectivo en bolivianos no cubre el equivalente de las cuotas seleccionadas en dólares.'
                        : 'El pago efectivo no puede ser menor al total de las cuotas seleccionadas.',
                ]);
            }

            $diferencia = round($montoEfectivo - $totalExigidoEnBolivianos, 2);

            $pago = Pago::create([
                // El pago siempre se recibe físicamente en bolivianos.
                'monto' => $montoEfectivo,
                'diferencia' => $diferencia,
                'tipo_moneda' => 'B',
                'tipo_cambio' => $esDolares ? round($tipoCambio, 5) : null,
                'fecha' => now()->toDateString(),
                'fecha_deposito' => $datos['fecha_deposito'],
                'nop' => $datos['nop'],
                'tipo_pago' => 'PC',
                'anexo' => trim($datos['glosa']),
                'estado' => 'AC',
            ]);

            foreach ($cuotas as $cuota) {
                $pago->pagosCuotas()->create([
                    'id_cuota_solicitud' => $cuota->id,
                    'nro_cuota' => $cuota->nro_cuota,
                    'monto' => $cuota->cuota_fija,
                    'fecha' => now()->toDateString(),
                    'estado' => 'AC',
                ]);

                $cuota->update(['estado' => 'AC']);
            }

            $capitalPagado = round((float) $cuotas->sum('amortizacion_cap'), 2);
            $saldoActual = max(0, round((float) $prestamoBloqueado->saldo_actual - $capitalPagado, 2));
            $quedanCuotas = $prestamoBloqueado->cuotas()->where('estado', 'PE')->exists();

            $prestamoBloqueado->update([
                'saldo_actual' => $saldoActual,
                'ultima_cuota' => max((int) $prestamoBloqueado->ultima_cuota, (int) $cuotas->max('nro_cuota')),
                'estado' => $quedanCuotas ? $prestamoBloqueado->estado : 'PA',
            ]);

            return ! $quedanCuotas;
        });

        return redirect()
            ->route(
                $prestamoCerrado ? 'prestamos.pagos.reporte' : 'prestamos.pagos',
                $prestamo
            )
            ->with('success', 'El pago de las cuotas fue guardado correctamente.');
    }

    public function storeTotal(StorePagoTotalRequest $request, Prestamo $prestamo)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $prestamo) {
            $prestamoBloqueado = Prestamo::query()
                ->with('tipo')
                ->whereKey($prestamo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $cuotasPendientes = $prestamoBloqueado->cuotas()
                ->where('estado', 'PE')
                ->orderBy('nro_cuota')
                ->lockForUpdate()
                ->get();

            if (
                $prestamoBloqueado->estado !== 'AC'
                || (float) $prestamoBloqueado->saldo_actual <= 0
                || $cuotasPendientes->isEmpty()
            ) {
                throw ValidationException::withMessages([
                    'monto_efectivo_total' => 'El préstamo ya no tiene un saldo pendiente para cancelar.',
                ])->errorBag('pagoTotal');
            }

            $saldoTotal = round((float) $prestamoBloqueado->saldo_actual, 2);
            $montoEfectivo = round((float) $datos['monto_efectivo_total'], 2);
            $esDolares = $prestamoBloqueado->tipo->tipo_moneda === 'SU';
            $tipoCambio = $esDolares ? (float) ($datos['tipo_cambio_total'] ?? 0) : 0;

            if ($esDolares && $tipoCambio <= 0) {
                throw ValidationException::withMessages([
                    'tipo_cambio_total' => 'Debe ingresar un tipo de cambio válido para el pago total.',
                ])->errorBag('pagoTotal');
            }

            $totalExigidoEnBolivianos = $esDolares
                ? round($saldoTotal * $tipoCambio, 2)
                : $saldoTotal;

            if ($montoEfectivo < $totalExigidoEnBolivianos) {
                throw ValidationException::withMessages([
                    'monto_efectivo_total' => $esDolares
                        ? 'El pago efectivo en bolivianos no cubre el saldo total convertido a bolivianos.'
                        : 'El pago efectivo no puede ser menor al saldo total del préstamo.',
                ])->errorBag('pagoTotal');
            }

            $pago = Pago::create([
                'monto' => $montoEfectivo,
                'diferencia' => round($montoEfectivo - $totalExigidoEnBolivianos, 2),
                'tipo_moneda' => 'B',
                'tipo_cambio' => $esDolares ? round($tipoCambio, 5) : null,
                'fecha' => now()->toDateString(),
                'fecha_deposito' => $datos['fecha_deposito_total'],
                'nop' => $datos['nop_total'],
                'tipo_pago' => 'PT',
                'anexo' => trim($datos['glosa_total']),
                'estado' => 'AC',
            ]);

            foreach ($cuotasPendientes as $cuota) {
                $pago->pagosCuotas()->create([
                    'id_cuota_solicitud' => $cuota->id,
                    'nro_cuota' => $cuota->nro_cuota,
                    'monto' => $cuota->amortizacion_cap,
                    'fecha' => now()->toDateString(),
                    'estado' => 'AC',
                ]);

                $cuota->update(['estado' => 'AC']);
            }

            $prestamoBloqueado->update([
                'saldo_actual' => 0,
                'ultima_cuota' => max(
                    (int) $prestamoBloqueado->ultima_cuota,
                    (int) $cuotasPendientes->max('nro_cuota')
                ),
                'estado' => 'PA',
            ]);
        });

        return redirect()
            ->route('prestamos.pagos.reporte', $prestamo)
            ->with('success', 'El pago total del préstamo fue guardado correctamente.');
    }

    public function reporte(Prestamo $prestamo)
    {
        return view('pagos.reporte', $this->datosReporte($prestamo));
    }

    public function reportePdf(Prestamo $prestamo)
    {
        $datos = $this->datosReporte($prestamo);

        return Pdf::loadView('pagos.pdf.reporte', $datos)
            ->setOption('isPhpEnabled', true)
            ->setPaper('letter', 'portrait')
            ->stream('Reporte_Pagos_'.$prestamo->nro_solicitud.'.pdf');
    }

    private function datosReporte(Prestamo $prestamo): array
    {
        $prestamo->load([
            'socio.institucion.grado',
            'tipo',
            'cuotas',
        ]);
        $idsCuotas = $prestamo->cuotas->pluck('id');
        $idsPagosCuotas = PagoCuota::query()
            ->whereIn('id_cuota_solicitud', $idsCuotas)
            ->distinct()
            ->pluck('id_pago');
        $idsPagosAmortizaciones = $prestamo->amortizacionesCapital()
            ->pluck('id_pago');
        $idsPagos = $idsPagosCuotas
            ->merge($idsPagosAmortizaciones)
            ->unique()
            ->values();

        $pagos = Pago::query()
            ->with([
                'amortizacionCapital',
                'pagosCuotas' => fn ($query) => $query
                    ->whereIn('id_cuota_solicitud', $idsCuotas)
                    ->orderBy('nro_cuota'),
            ])
            ->whereIn('id', $idsPagos)
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $pagos->each(function (Pago $pago): void {
            $esAmortizacion = $pago->tipo_pago === 'AM'
                && $pago->amortizacionCapital !== null;

            $pago->setAttribute('es_amortizacion_reporte', $esAmortizacion);
            $pago->setAttribute(
                'tipo_reporte',
                match ($pago->tipo_pago) {
                    'AM' => 'AMORTIZACIÓN',
                    'PT' => 'PAGO TOTAL',
                    default => 'PAGO POR CUOTAS',
                }
            );
            $pago->setAttribute(
                'cuotas_reporte',
                $esAmortizacion
                    ? 'No aplica'
                    : ($pago->pagosCuotas->pluck('nro_cuota')->implode(', ') ?: '-')
            );
            $pago->setAttribute(
                'monto_aplicado_reporte',
                $esAmortizacion
                    ? round((float) $pago->amortizacionCapital->monto_capital, 2)
                    : round((float) $pago->pagosCuotas->sum('monto'), 2)
            );
            $pago->setAttribute(
                'glosa_reporte',
                $esAmortizacion
                    ? 'Autorización: '.$pago->amortizacionCapital->autorizacion
                        .($pago->amortizacionCapital->observaciones
                            ? ' / Obs.: '.$pago->amortizacionCapital->observaciones
                            : '')
                    : ($pago->anexo ?: '-')
            );
        });

        $cuotasDetalle = $prestamo->cuotas->map(function ($cuota) use ($prestamo) {
            $pagosCuotaActivos = $cuota->pagosCuotas->filter(
                fn ($pagoCuota) => $pagoCuota->estado === 'AC'
                    && $pagoCuota->pago?->estado === 'AC'
            );

            $cuota->setAttribute(
                'monto_cancelado_reporte',
                round((float) $pagosCuotaActivos->sum('monto'), 2)
            );
            $cuota->setAttribute(
                'saldo_capital_reporte',
                $cuota->estado === 'AC'
                    ? round((float) $cuota->saldo, 2)
                    : round((float) $prestamo->saldo_actual, 2)
            );
            $cuota->setAttribute(
                'situacion_reporte',
                $cuota->estado === 'AC' ? 'Cancelado' : ''
            );
            $cuota->setAttribute(
                'observaciones_reporte',
                $pagosCuotaActivos
                    ->pluck('pago.anexo')
                    ->filter()
                    ->unique()
                    ->implode(' / ')
            );

            return $cuota;
        });

        $amortizacionesReporte = $pagos
            ->where('es_amortizacion_reporte', true)
            ->values();

        return [
            'prestamo' => $prestamo,
            'pagos' => $pagos,
            'cuotasDetalle' => $cuotasDetalle,
            'amortizacionesReporte' => $amortizacionesReporte,
            'cuotasPagadas' => $prestamo->cuotas->where('estado', 'AC')->count(),
            'cuotasPendientes' => $prestamo->cuotas->where('estado', 'PE')->count(),
            'totalEfectivo' => round((float) $pagos->sum('monto'), 2),
            'totalAplicado' => round((float) $pagos->sum('monto_aplicado_reporte'), 2),
            'totalDiferencia' => round((float) $pagos->sum('diferencia'), 2),
            'totalMontoCancelado' => round(
                (float) $cuotasDetalle->sum('monto_cancelado_reporte'),
                2
            ),
            'totalAmortizadoCapital' => round(
                (float) $amortizacionesReporte->sum('monto_aplicado_reporte'),
                2
            ),
            'totalEfectivoAmortizaciones' => round(
                (float) $amortizacionesReporte->sum('monto'),
                2
            ),
            'esPrestamoDolares' => $prestamo->tipo?->tipo_moneda === 'SU',
        ];
    }
}
