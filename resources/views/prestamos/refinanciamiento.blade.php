<x-app-layout>
    <x-slot name="header">Refinanciamiento de Préstamo</x-slot>

    @php
        $esDolares = $prestamo->tipo->tipo_moneda === 'SU';
        $moneda = $esDolares ? '$us' : 'Bs';
    @endphp

    <form id="formRefinanciamiento" method="POST"
        action="{{ route('prestamos.refinanciamiento.store', $prestamo) }}"
        novalidate
        data-url-simular="{{ route('prestamos.simular') }}"
        data-ruta-tipo-cambio="{{ route('prestamos.tipo-cambio', ['fecha' => '__FECHA__']) }}"
        data-saldo-actual="{{ number_format((float) $prestamo->saldo_actual, 2, '.', '') }}"
        data-moneda="{{ $prestamo->tipo->tipo_moneda }}"
        data-tipo="{{ $prestamo->tipo->id_tasa }}"
        data-porcentaje="{{ $prestamo->tipo->porcentaje }}"
        data-itf="{{ $prestamo->tipo->itf }}"
        data-papeleria="{{ $prestamo->tipo->papeleria }}"
        data-min-defensa="{{ $prestamo->tipo->min_defensa }}">
        @csrf

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card shadow-sm mb-4 border-warning">
                    <div class="card-header bg-warning text-dark fw-semibold">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Préstamo que será refinanciado
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted">Solicitud</small>
                                <div class="fw-bold">{{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Solicitante</small>
                                <div class="fw-bold">
                                    {{ $prestamo->socio->institucion->papeleta }} -
                                    {{ trim($prestamo->socio->paterno.' '.$prestamo->socio->materno.' '.$prestamo->socio->nombres) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Tipo de préstamo</small>
                                <div class="fw-bold">{{ $prestamo->tipo->descripcion_tasa }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Saldo capital</small>
                                <div class="fw-bold text-danger">{{ $moneda }} {{ number_format($prestamo->saldo_actual, 2) }}</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted">Pagadas</small>
                                <div class="fw-bold text-success">{{ $prestamo->cuotas_pagadas_count }}</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted">Pendientes</small>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="fw-bold text-warning">{{ $prestamo->cuotas_pendientes_count }}</span>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-ver-cuotas"
                                        data-url="{{ route('prestamos.detalle', $prestamo) }}"
                                    >
                                        <i class="bi bi-eye me-1"></i>Ver Cuotas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">Datos financieros</div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="nuevoMontoRefinanciamiento" class="form-label">Nuevo monto</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $moneda }}</span>
                                    <input type="text" inputmode="decimal"
                                        class="form-control text-end @error('nuevo_monto') is-invalid @enderror"
                                        id="nuevoMontoRefinanciamiento" name="nuevo_monto"
                                        value="{{ old('nuevo_monto') }}">
                                </div>
                                <div class="form-text">
                                    Máximo: {{ $moneda }} {{ number_format($prestamo->tipo->monto_max, 2) }}
                                </div>
                                @error('nuevo_monto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="plazoRefinanciamiento" class="form-label">Plazo (meses)</label>
                                <input type="text" inputmode="numeric"
                                    class="form-control @error('plazo') is-invalid @enderror"
                                    id="plazoRefinanciamiento" name="plazo"
                                    value="{{ old('plazo') }}">
                                <div class="form-text">Máximo: {{ $prestamo->tipo->plazo_max }} meses</div>
                                @error('plazo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="tipoCambioRefinanciamiento" class="form-label">Tipo de cambio</label>
                                <div class="input-group">
                                    <input type="text" inputmode="decimal"
                                        class="form-control text-end @error('tipo_cambio') is-invalid @enderror"
                                        id="tipoCambioRefinanciamiento" name="tipo_cambio"
                                        value="{{ old('tipo_cambio', number_format((float) $prestamo->tipo_cambio, 5, '.', '')) }}">
                                    <button type="button" class="btn btn-outline-secondary" id="btnTipoCambioRefinanciamiento">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                @error('tipo_cambio')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="asientoRefinanciamiento" class="form-label">N.º Asiento</label>
                                <div class="input-group">
                                    <span class="input-group-text">EG-</span>
                                    <input class="form-control @error('asiento') is-invalid @enderror"
                                        id="asientoRefinanciamiento" name="asiento"
                                        value="{{ old('asiento') }}">
                                </div>
                                @error('asiento')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="fechaRefinanciamiento" class="form-label">Fecha depósito</label>
                                <input type="date" class="form-control @error('fechaPrestamo') is-invalid @enderror"
                                    id="fechaRefinanciamiento" name="fechaPrestamo"
                                    value="{{ old('fechaPrestamo', now()->format('Y-m-d')) }}">
                                @error('fechaPrestamo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="motivoRefinanciamiento" class="form-label">Motivo</label>
                                <textarea class="form-control @error('motivo') is-invalid @enderror"
                                    id="motivoRefinanciamiento" name="motivo"
                                    rows="3">{{ old('motivo', 'REFINANCIAMIENTO DE PRÉSTAMO REGULAR') }}</textarea>
                                @error('motivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">Garantes del préstamo refinanciado</div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Introduzca el código de papeleta y seleccione cada garante de la lista.
                        </p>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <x-scas.papeleta-search
                                    name="id_garante1"
                                    label="Código de papeleta - Primer garante"
                                    :value="old('id_garante1', $prestamo->id_garante1)" />
                            </div>
                            <div class="col-md-6">
                                <x-scas.papeleta-search
                                    name="id_garante2"
                                    label="Código de papeleta - Segundo garante"
                                    :value="old('id_garante2', $prestamo->id_garante2)" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">Cronograma del nuevo préstamo</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Capital</th>
                                        <th class="text-end">Interés</th>
                                        <th class="text-end">Min. Defensa</th>
                                        <th class="text-end">ITF</th>
                                        <th class="text-end">Otros</th>
                                        <th class="text-end">Cuota</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody id="cronogramaRefinanciamiento">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Ingrese el nuevo monto y plazo para calcular el cronograma.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>
                        Aplicar refinanciamiento
                    </button>
                    <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </a>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow sticky-top" style="top: 90px;">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Resumen del refinanciamiento
                    </div>
                    <div class="card-body">
                        <small class="text-muted">Monto del nuevo préstamo</small>
                        <h5 id="resumenNuevoMonto">-</h5>
                        <hr>
                        <small class="text-muted">Saldo absorbido del préstamo anterior</small>
                        <h5 class="text-warning">{{ $moneda }} {{ number_format($prestamo->saldo_actual, 2) }}</h5>
                        <hr>
                        <small class="text-muted">Desembolso neto</small>
                        <h4 id="resumenDesembolso" class="text-success">-</h4>
                        @if($esDolares)
                            <small class="text-muted">Desembolso equivalente en bolivianos</small>
                            <div id="resumenDesembolsoBs" class="fw-bold">-</div>
                        @endif
                        <hr>
                        <small class="text-muted">Plazo</small>
                        <div id="resumenPlazo" class="fw-bold">-</div>
                        <small class="text-muted d-block mt-3">Cuota mensual estimada</small>
                        <div id="resumenCuota" class="fw-bold">-</div>
                        <small class="text-muted d-block mt-3">Interés total</small>
                        <div id="resumenInteres" class="fw-bold">-</div>
                        <small class="text-muted d-block mt-3">Total a pagar</small>
                        <div id="resumenTotal" class="fw-bold">-</div>
                        <div class="alert alert-warning mt-4 mb-0 small">
                            El préstamo actual quedará cerrado con estado
                            <strong>CE</strong> y el nuevo cronograma se calculará sobre el monto total.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="contenedorDetallePrestamo"></div>
</x-app-layout>
