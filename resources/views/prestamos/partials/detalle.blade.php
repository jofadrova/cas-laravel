@php
    $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
@endphp

<div class="modal fade" id="modalDetallePrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Detalle del préstamo
                    </h5>
                    <small>Solicitud N.º <strong>{{ $prestamo->nro_solicitud }}</strong></small>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-semibold">Datos generales</div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th width="40%">Socio</th>
                                        <td>{{ trim($prestamo->socio->nombres.' '.$prestamo->socio->paterno.' '.$prestamo->socio->materno) }}</td>
                                    </tr>
                                    <tr>
                                        <th>C.I.</th>
                                        <td>{{ $prestamo->socio->nro_doc }} {{ $prestamo->socio->expedido }}</td>
                                    </tr>
                                    <tr>
                                        <th>Papeleta</th>
                                        <td>{{ $prestamo->socio->institucion->papeleta }}</td>
                                    </tr>
                                    <tr>
                                        <th>Grado</th>
                                        <td>{{ $prestamo->socio->institucion->grado->grado }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo</th>
                                        <td>{{ $prestamo->tipo->descripcion_tasa }}</td>
                                    </tr>
                                    <tr>
                                        <th>Estado</th>
                                        <td>
                                            @if($prestamo->estado === 'AC')
                                                <span class="badge bg-success">ACTIVO</span>
                                            @elseif($prestamo->estado === 'PA')
                                                <span class="badge bg-danger">CANCELADO</span>
                                            @elseif($prestamo->estado === 'CE')
                                                <span class="badge bg-dark">CERRADO POR REFINANCIAMIENTO</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $prestamo->estado }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($prestamo->prestamoOrigen)
                                        <tr>
                                            <th>PrÃ©stamo anterior</th>
                                            <td>Solicitud N.Âº {{ $prestamo->prestamoOrigen->nro_solicitud }}</td>
                                        </tr>
                                    @elseif($prestamo->estado === 'CE' && $prestamo->refinanciamientos->isNotEmpty())
                                        <tr>
                                            <th>Nuevo prÃ©stamo</th>
                                            <td>Solicitud N.Âº {{ $prestamo->refinanciamientos->first()->nro_solicitud }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light fw-semibold">Datos económicos</div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <th>Monto</th>
                                        <td class="text-end">{{ $monedaPrestamo }} {{ number_format($prestamo->monto, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Saldo actual</th>
                                        <td class="text-end">{{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Interés</th>
                                        <td class="text-end">{{ number_format($prestamo->interes, 2) }} %</td>
                                    </tr>
                                    <tr>
                                        <th>Periodo</th>
                                        <td class="text-end">{{ $prestamo->periodo }} cuotas</td>
                                    </tr>
                                    <tr>
                                        <th>Cuotas</th>
                                        <td class="text-end">
                                            {{ $cuotasPagadas->count() }} pagadas /
                                            {{ $cuotasPendientes->count() }} pendientes
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de cambio inicial</th>
                                        <td class="text-end">
                                            {{ $esPrestamoDolares ? number_format($prestamo->tipo_cambio, 5) : 'No aplica' }}
                                        </td>
                                    </tr>
                                    @if($prestamo->refinanciado)
                                        <tr>
                                            <th>Saldo absorbido</th>
                                            <td class="text-end">{{ $monedaPrestamo }} {{ number_format($prestamo->saldo_refinanciado, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Desembolso neto</th>
                                            <td class="text-end">{{ $monedaPrestamo }} {{ number_format($prestamo->monto_desembolso_refinanciamiento, 2) }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light fw-semibold">Garantes</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Primer garante</strong>
                                        <hr>
                                        @if($prestamo->garante1)
                                            {{ trim($prestamo->garante1->nombres.' '.$prestamo->garante1->paterno.' '.$prestamo->garante1->materno) }}
                                        @else
                                            <span class="text-muted">No registrado</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Segundo garante</strong>
                                        <hr>
                                        @if($prestamo->garante2)
                                            {{ trim($prestamo->garante2->nombres.' '.$prestamo->garante2->paterno.' '.$prestamo->garante2->materno) }}
                                        @else
                                            <span class="text-muted">No registrado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white fw-semibold">
                                <i class="bi bi-check-circle me-2"></i>
                                Cuotas pagadas ({{ $cuotasPagadas->count() }})
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Cuota</th>
                                            <th class="text-center">Periodo</th>
                                            <th class="text-end">Cuota fija</th>
                                            <th class="text-end">Capital</th>
                                            <th class="text-end">Interés</th>
                                            <th class="text-end">Otros cargos</th>
                                            <th class="text-end">Saldo</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cuotasPagadas as $cuota)
                                            @php
                                                $otrosCargos =
                                                    (float) $cuota->min_defensa
                                                    + (float) $cuota->itf
                                                    + (float) $cuota->papel
                                                    + (float) $cuota->comision_mindef
                                                    + (float) $cuota->rep_formulario;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $cuota->nro_cuota }}</td>
                                                <td class="text-center">{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                                                <td class="text-end">{{ number_format($cuota->cuota_fija, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->interes, 2) }}</td>
                                                <td class="text-end">{{ number_format($otrosCargos, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->saldo, 2) }}</td>
                                                <td class="text-center"><span class="badge bg-success">PAGADA</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-3">
                                                    No existen cuotas pagadas.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark fw-semibold">
                                <i class="bi bi-clock me-2"></i>
                                Cuotas por pagar ({{ $cuotasPendientes->count() }})
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Cuota</th>
                                            <th class="text-center">Periodo</th>
                                            <th class="text-end">Cuota fija</th>
                                            <th class="text-end">Capital</th>
                                            <th class="text-end">Interés</th>
                                            <th class="text-end">Otros cargos</th>
                                            <th class="text-end">Saldo</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cuotasPendientes as $cuota)
                                            @php
                                                $otrosCargos =
                                                    (float) $cuota->min_defensa
                                                    + (float) $cuota->itf
                                                    + (float) $cuota->papel
                                                    + (float) $cuota->comision_mindef
                                                    + (float) $cuota->rep_formulario;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $cuota->nro_cuota }}</td>
                                                <td class="text-center">{{ sprintf('%02d', $cuota->mes) }}/{{ $cuota->gestion }}</td>
                                                <td class="text-end">{{ number_format($cuota->cuota_fija, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->amortizacion_cap, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->interes, 2) }}</td>
                                                <td class="text-end">{{ number_format($otrosCargos, 2) }}</td>
                                                <td class="text-end">{{ number_format($cuota->saldo, 2) }}</td>
                                                <td class="text-center"><span class="badge bg-warning text-dark">PENDIENTE</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-3">
                                                    No existen cuotas pendientes.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($amortizaciones->isNotEmpty())
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-dark fw-semibold">
                                    <i class="bi bi-arrow-down-circle me-2"></i>
                                    Amortizaciones de capital ({{ $amortizaciones->count() }})
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Modalidad</th>
                                                <th class="text-end">Efectivo Bs</th>
                                                <th class="text-end">Capital amortizado</th>
                                                <th class="text-end">T.C.</th>
                                                <th class="text-end">Saldo anterior</th>
                                                <th class="text-end">Saldo nuevo</th>
                                                <th class="text-end">Cuota anterior</th>
                                                <th class="text-end">Cuota nueva</th>
                                                <th class="text-center">Plazo</th>
                                                <th>Autorización / Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($amortizaciones as $amortizacion)
                                                <tr>
                                                    <td>{{ $amortizacion->fecha->format('d/m/Y') }}</td>
                                                    <td>
                                                        {{ $amortizacion->tipo_recalculo === 'CUOTA'
                                                            ? 'Reducir cuota'
                                                            : 'Reducir plazo' }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($amortizacion->monto_efectivo, 2) }}</td>
                                                    <td class="text-end">
                                                        {{ $monedaPrestamo }} {{ number_format($amortizacion->monto_capital, 2) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ $amortizacion->tipo_cambio
                                                            ? number_format($amortizacion->tipo_cambio, 5)
                                                            : '-' }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($amortizacion->saldo_anterior, 2) }}</td>
                                                    <td class="text-end">{{ number_format($amortizacion->saldo_nuevo, 2) }}</td>
                                                    <td class="text-end">{{ number_format($amortizacion->cuota_anterior, 2) }}</td>
                                                    <td class="text-end">{{ number_format($amortizacion->cuota_nueva, 2) }}</td>
                                                    <td class="text-center">
                                                        {{ $amortizacion->periodo_anterior }}
                                                        →
                                                        {{ $amortizacion->periodo_nuevo }}
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
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <a href="{{ route('prestamos.detalle.pdf', $prestamo) }}"
                    class="btn btn-outline-primary" target="_blank">
                    <i class="bi bi-printer me-1"></i>
                    Imprimir préstamo
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
