<x-app-layout>
    <x-slot name="header">Reporte de Pagos</x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @php
        $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-info-circle me-2"></i>
            Resumen del Préstamo
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="text-muted small">Solicitud</label>
                    <div class="fw-semibold">
                        {{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Estado</label>
                    <div>
                        @if($prestamo->estado === 'AC')
                            <span class="badge bg-success">ACTIVO</span>
                        @elseif($prestamo->estado === 'PA')
                            <span class="badge bg-danger">CANCELADO</span>
                        @else
                            <span class="badge bg-secondary">{{ $prestamo->estado }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Tipo de préstamo</label>
                    <div class="fw-semibold">
                        {{ $prestamo->tipo?->descripcion_tasa ?? 'Tipo no registrado' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Socio</label>
                    <div class="fw-semibold">
                        {{ trim(
                            ($prestamo->socio?->paterno ?? '').' '.
                            ($prestamo->socio?->materno ?? '').' '.
                            ($prestamo->socio?->nombres ?? '')
                        ) ?: 'No registrado' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Papeleta</label>
                    <div class="fw-semibold">
                        {{ $prestamo->socio?->institucion?->papeleta ?? 'No registrada' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Grado</label>
                    <div class="fw-semibold">
                        {{ $prestamo->socio?->institucion?->grado?->grado ?? 'No registrado' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Monto original</label>
                    <div class="fw-bold text-primary">
                        {{ $monedaPrestamo }} {{ number_format($prestamo->monto, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Saldo actual</label>
                    <div class="fw-bold text-danger">
                        {{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Cuotas</label>
                    <div class="fw-semibold">
                        {{ $cuotasPagadas }} pagadas / {{ $cuotasPendientes }} pendientes
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Total efectivo registrado</label>
                    <div class="fw-bold text-success">
                        Bs {{ number_format($totalEfectivo, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <i class="bi bi-receipt me-2"></i>
            Detalle de Cuotas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center">Cuota</th>
                            <th class="text-center">Periodo<br>mes/año</th>
                            <th class="text-end">Cuota fija</th>
                            <th class="text-end">Amort.<br>capital</th>
                            <th class="text-end">Interés</th>
                            <th class="text-end">Min. Def.</th>
                            <th class="text-end">Contingencias</th>
                            <th class="text-end">Interés días</th>
                            <th class="text-end">Saldo<br>capital</th>
                            <th>Situación</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuotasDetalle as $cuota)
                            <tr>
                                <td class="text-center">{{ $cuota->nro_cuota }}</td>
                                <td class="text-center">
                                    {{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}
                                </td>
                                <td class="text-end">{{ number_format($cuota->cuota_fija, 2) }}</td>
                                <td class="text-end">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                                <td class="text-end">{{ number_format($cuota->interes, 2) }}</td>
                                <td class="text-end">{{ number_format($cuota->min_defensa, 2) }}</td>
                                <td class="text-end">{{ number_format($cuota->itf, 2) }}</td>
                                <td class="text-end">{{ number_format($cuota->papel, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($cuota->saldo_capital_reporte, 2) }}
                                </td>
                                <td>
                                    @if($cuota->situacion_reporte)
                                        <span class="badge bg-success">
                                            {{ $cuota->situacion_reporte }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $cuota->observaciones_reporte ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    No existen cuotas registradas para este préstamo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($cuotasDetalle->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="8" class="text-end">Total cancelado:</td>
                                <td class="text-end">
                                    {{ number_format($totalMontoCancelado, 2) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($amortizacionesReporte->isNotEmpty())
                <div class="card border-warning mt-4">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="bi bi-arrow-down-circle me-2"></i>
                        Amortizaciones de Capital
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">N.º</th>
                                    <th>Fecha</th>
                                    <th>Modalidad</th>
                                    <th class="text-end">Efectivo recibido</th>
                                    <th class="text-end">Capital amortizado</th>
                                    <th class="text-end">T.C.</th>
                                    <th class="text-end">Saldo anterior</th>
                                    <th class="text-end">Saldo nuevo</th>
                                    <th>Autorización / Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($amortizacionesReporte as $pago)
                                    @php($amortizacion = $pago->amortizacionCapital)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            {{ ($pago->fecha_deposito ?? $pago->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            {{ $amortizacion->tipo_recalculo === 'CUOTA'
                                                ? 'Reducir cuota'
                                                : 'Reducir plazo' }}
                                        </td>
                                        <td class="text-end">
                                            Bs {{ number_format($pago->monto, 2) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $monedaPrestamo }}
                                            {{ number_format($amortizacion->monto_capital, 2) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $pago->tipo_cambio
                                                ? number_format($pago->tipo_cambio, 5)
                                                : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $monedaPrestamo }}
                                            {{ number_format($amortizacion->saldo_anterior, 2) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $monedaPrestamo }}
                                            {{ number_format($amortizacion->saldo_nuevo, 2) }}
                                        </td>
                                        <td>
                                            <strong>Aut.:</strong> {{ $amortizacion->autorizacion }}
                                            @if($amortizacion->observaciones)
                                                <br><strong>Obs.:</strong> {{ $amortizacion->observaciones }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Totales:</td>
                                    <td class="text-end">
                                        Bs {{ number_format($totalEfectivoAmortizaciones, 2) }}
                                    </td>
                                    <td class="text-end">
                                        {{ $monedaPrestamo }}
                                        {{ number_format($totalAmortizadoCapital, 2) }}
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-check-circle me-1"></i>ACEPTAR
                </a>
                <a href="{{ route('prestamos.pagos.reporte.pdf', $prestamo) }}"
                    class="btn btn-danger" target="_blank">
                    <i class="bi bi-printer me-1"></i>IMPRIMIR
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
