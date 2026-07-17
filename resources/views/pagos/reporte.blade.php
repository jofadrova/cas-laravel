<x-app-layout>
    <x-slot name="header">Reporte de Pagos</x-slot>

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
                    <div class="fw-semibold">{{ $prestamo->tipo->descripcion_tasa }}</div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Socio</label>
                    <div class="fw-semibold">
                        {{ trim($prestamo->socio->paterno.' '.$prestamo->socio->materno.' '.$prestamo->socio->nombres) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Papeleta</label>
                    <div class="fw-semibold">{{ $prestamo->socio->institucion->papeleta }}</div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Grado</label>
                    <div class="fw-semibold">{{ $prestamo->socio->institucion->grado->grado }}</div>
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
                    <div class="fw-bold text-success">Bs {{ number_format($totalEfectivo, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <i class="bi bi-receipt me-2"></i>
            Detalle de Pagos Realizados
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">N.º</th>
                            <th>Fecha</th>
                            <th>Tipo de pago</th>
                            <th>Cuotas</th>
                            <th class="text-end">Monto aplicado</th>
                            <th class="text-end">Efectivo recibido</th>
                            <th class="text-end">T.C.</th>
                            <th class="text-end">Diferencia</th>
                            <th>Glosa</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            @php
                                $esAmortizacion = $pago->tipo_pago === 'AM' && $pago->amortizacionCapital;
                                $cuotas = $esAmortizacion
                                    ? 'No aplica'
                                    : $pago->pagosCuotas->pluck('nro_cuota')->implode(', ');
                                $montoAplicado = $esAmortizacion
                                    ? (float) $pago->amortizacionCapital->monto_capital
                                    : (float) $pago->pagosCuotas->sum('monto');
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                                <td>
                                    @if($esAmortizacion)
                                        <span class="badge bg-warning text-dark">AMORTIZACIÓN</span>
                                    @elseif($pago->tipo_pago === 'PT')
                                        <span class="badge bg-primary">PAGO TOTAL</span>
                                    @else
                                        <span class="badge bg-info text-dark">PAGO POR CUOTAS</span>
                                    @endif
                                </td>
                                <td>{{ $cuotas ?: '-' }}</td>
                                <td class="text-end">
                                    {{ $monedaPrestamo }} {{ number_format($montoAplicado, 2) }}
                                </td>
                                <td class="text-end">Bs {{ number_format($pago->monto, 2) }}</td>
                                <td class="text-end">
                                    {{ $pago->tipo_cambio ? number_format($pago->tipo_cambio, 5) : '-' }}
                                </td>
                                <td class="text-end">Bs {{ number_format($pago->diferencia, 2) }}</td>
                                <td>
                                    @if($esAmortizacion)
                                        <strong>Autorización:</strong> {{ $pago->amortizacionCapital->autorizacion }}
                                        @if($pago->amortizacionCapital->observaciones)
                                            <br><strong>Obs.:</strong> {{ $pago->amortizacionCapital->observaciones }}
                                        @endif
                                    @else
                                        {{ $pago->anexo ?: '-' }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $pago->estado === 'AC' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $pago->estado === 'AC' ? 'REGISTRADO' : $pago->estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No existen pagos registrados para este préstamo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($pagos->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">Totales:</td>
                                <td class="text-end">Bs {{ number_format($totalEfectivo, 2) }}</td>
                                <td></td>
                                <td class="text-end">Bs {{ number_format($totalDiferencia, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

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
