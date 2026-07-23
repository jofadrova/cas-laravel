<x-app-layout>
    <x-slot name="header">Reprogramar Préstamo</x-slot>

    @php
        $esDolares = $prestamo->tipo->tipo_moneda === 'SU';
        $moneda = $esDolares ? '$us' : 'Bs';
        $primeraPendiente = $cuotasPendientes->first();
        $otrosCargosPrimera =
            (float) $primeraPendiente->itf
            + (float) $primeraPendiente->papel
            + (float) $primeraPendiente->comision_mindef
            + (float) $primeraPendiente->rep_formulario;
        $ultimaPagada = (int) ($cuotasPagadas->max('nro_cuota') ?? 0);
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
                    <div class="fw-semibold">{{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Asociado</label>
                    <div class="fw-semibold">
                        {{ trim($prestamo->socio->paterno.' '.$prestamo->socio->materno.' '.$prestamo->socio->nombres) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Tipo de préstamo</label>
                    <div class="fw-semibold">{{ $prestamo->tipo->descripcion_tasa }}</div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Saldo capital</label>
                    <div class="fw-bold text-danger">
                        {{ $moneda }} {{ number_format($prestamo->saldo_actual, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Plazo actual</label>
                    <div class="fw-semibold">{{ $prestamo->periodo }} cuotas</div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Cuotas pagadas</label>
                    <div class="fw-bold text-success">{{ $cuotasPagadas->count() }}</div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small">Cuotas pendientes</label>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold text-warning">{{ $cuotasPendientes->count() }}</span>
                        <button type="button"
                            class="btn btn-sm btn-outline-primary btn-ver-cuotas"
                            data-url="{{ route('prestamos.detalle', $prestamo) }}">
                            <i class="bi bi-eye me-1"></i>Ver Cuotas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formReprogramacionPrestamo" method="POST"
        action="{{ route('prestamos.reprogramacion.store', $prestamo) }}"
        novalidate
        data-moneda="{{ $moneda }}"
        data-saldo="{{ number_format((float) $prestamo->saldo_actual, 2, '.', '') }}"
        data-tasa="{{ number_format((float) $prestamo->interes / 100, 8, '.', '') }}"
        data-min-defensa="{{ number_format((float) $prestamo->min_defensa / 100, 8, '.', '') }}"
        data-otros-cargos="{{ number_format($otrosCargosPrimera, 2, '.', '') }}"
        data-pendientes-actuales="{{ $cuotasPendientes->count() }}"
        data-ultima-pagada="{{ $ultimaPagada }}"
        data-plazo-maximo="{{ $prestamo->tipo->plazo_max }}">
        @csrf

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-calendar2-range me-2"></i>
                        Datos de la Reprogramación
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            Las cuotas pagadas no serán modificadas. El saldo capital se distribuirá
                            únicamente entre las nuevas cuotas pendientes.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="cuotasPendientesNuevas" class="form-label">
                                    Nueva cantidad de cuotas pendientes
                                </label>
                                <input type="text" inputmode="numeric"
                                    class="form-control @error('cuotas_pendientes_nuevas') is-invalid @enderror"
                                    id="cuotasPendientesNuevas" name="cuotas_pendientes_nuevas"
                                    value="{{ old('cuotas_pendientes_nuevas') }}">
                                <div class="form-text">
                                    Debe ser mayor a {{ $cuotasPendientes->count() }}.
                                    Plazo total máximo: {{ $prestamo->tipo->plazo_max }}.
                                </div>
                                @error('cuotas_pendientes_nuevas')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="autorizacionReprogramacion" class="form-label">
                                    Autorización de Cartera
                                </label>
                                <textarea class="form-control @error('autorizacion') is-invalid @enderror"
                                    id="autorizacionReprogramacion" name="autorizacion"
                                    rows="3">{{ old('autorizacion') }}</textarea>
                                @error('autorizacion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="observacionesReprogramacion" class="form-label">
                                    Observaciones
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror"
                                    id="observacionesReprogramacion" name="observaciones"
                                    rows="3">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Aplicar reprogramación
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow sticky-top" style="top: 90px;">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-calculator me-2"></i>
                        Resultado estimado
                    </div>
                    <div class="card-body">
                        <small class="text-muted">Saldo que será reprogramado</small>
                        <h5>{{ $moneda }} {{ number_format($prestamo->saldo_actual, 2) }}</h5>
                        <hr>
                        <small class="text-muted">Cuotas pendientes actuales</small>
                        <div class="fw-bold">{{ $cuotasPendientes->count() }}</div>
                        <small class="text-muted d-block mt-3">Nuevas cuotas pendientes</small>
                        <div class="fw-bold" id="resumenCuotasReprogramadas">-</div>
                        <small class="text-muted d-block mt-3">Nuevo plazo total</small>
                        <div class="fw-bold" id="resumenPlazoReprogramado">-</div>
                        <small class="text-muted d-block mt-3">Nueva cuota estimada</small>
                        <h5 class="text-success" id="resumenCuotaReprogramada">-</h5>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div id="contenedorDetallePrestamo"></div>
</x-app-layout>
