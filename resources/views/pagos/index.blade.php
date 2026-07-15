<x-app-layout>
    <div class="container-fluid">
        <h1 class="h2 mb-4">Registro de Pago de Préstamo</h1>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i>
                Registro de Pago de Préstamo
            </div>
           <div class="card-body">
                <div class="card border-success mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-info-circle me-2 text-success"></i>
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
                                    @if($prestamo->estado == 'AC')
                                        <span class="badge bg-success">ACTIVO</span>
                                    @else
                                        <span class="badge bg-secondary">CANCELADO</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Tipo de préstamo</label>
                                <div class="fw-semibold">
                                    {{ $prestamo->tipo->descripcion_tasa }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Socio</label>
                                <div class="fw-semibold">
                                    {{ $prestamo->socio->paterno }}
                                    {{ $prestamo->socio->materno }}
                                    {{ $prestamo->socio->nombres }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Papeleta</label>
                                <div class="fw-semibold">
                                    {{ $prestamo->socio->institucion->papeleta }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Grado</label>
                                <div class="fw-semibold">
                                    {{ $prestamo->socio->institucion->grado->grado }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="text-muted small">Monto</label>
                                <div class="fw-bold text-primary">
                                    Bs {{ number_format($prestamo->monto,2) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Saldo</label>
                                <div class="fw-bold text-danger">
                                    Bs {{ number_format($prestamo->saldo_actual,2) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Cuotas</label>
                                <div class="fw-semibold">
                                    {{ $cuotasPagadas }} pagadas
                                    /
                                    {{ $cantidadCuotasPendientes }} pendientes
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-coin me-2"></i>
                    Operaciones de Pago
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="tabsPagos" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cuotas-tab" data-bs-toggle="tab" data-bs-target="#cuotas" type="button" role="tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                Pago por Cuotas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link"  id="total-tab" data-bs-toggle="tab" data-bs-target="#total" type="button" role="tab">
                                <i class="bi bi-wallet2 me-2"></i>
                                Pago Total
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-3">
                        <div class="tab-pane fade show active" id="cuotas" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40"></th>
                                            <th>Cuota</th>
                                            <th>Periodo</th>
                                            <th class="text-end">Capital</th>
                                            <th class="text-end">Interés</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cuotasPendientes as $cuota)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input">
                                                </td>
                                                <td>{{ $cuota->nro_cuota }}</td>
                                                <td>{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                                                <td class="text-end">{{ number_format($cuota->amortizacion_cap,2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->interes,2) }}</td>
                                                <td class="text-end fw-bold">{{ number_format($cuota->cuota_fija,2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    No existen cuotas pendientes para cobrar.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- Aquí irá la tabla de cuotas --}}

                        </div>
                        <div class="tab-pane fade" id="total" role="tabpanel">
                            <div class="alert alert-info mb-0">

                                Pago total del préstamo.

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
