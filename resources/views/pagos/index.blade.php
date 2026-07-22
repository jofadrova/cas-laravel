<x-app-layout>
    <x-slot name="header">Registro de Pago de Préstamo</x-slot>
    @php
        $esPrestamoDolares = $prestamo->tipo->tipo_moneda === 'SU';
        $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
        $tipoCambioPrestamo = (float) $prestamo->tipo_cambio;
    @endphp
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
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
                                    {{ $monedaPrestamo }} {{ number_format($prestamo->monto,2) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Saldo</label>
                                <div class="fw-bold text-danger">
                                    {{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual,2) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Cuotas</label>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-semibold">
                                        {{ $cuotasPagadas }} pagadas
                                        /
                                        {{ $cantidadCuotasPendientes }} pendientes
                                    </span>
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
                            <button class="nav-link {{ $errors->pagoTotal->any() ? '' : 'active' }}" id="cuotas-tab" data-bs-toggle="tab" data-bs-target="#cuotas" type="button" role="tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                Pago por Cuotas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $errors->pagoTotal->any() ? 'active' : '' }}" id="total-tab" data-bs-toggle="tab" data-bs-target="#total" type="button" role="tab">
                                <i class="bi bi-wallet2 me-2"></i>
                                Pago Total
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-3">
                        <div class="tab-pane fade {{ $errors->pagoTotal->any() ? '' : 'show active' }}" id="cuotas" role="tabpanel">
                            <form id="formPagoCuotas" method="POST" action="{{ route('prestamos.pagos.store', $prestamo) }}" novalidate
                                data-moneda-prestamo="{{ $prestamo->tipo->tipo_moneda }}"
                                data-tipo-cambio="{{ number_format($tipoCambioPrestamo, 5, '.', '') }}">
                                @csrf
                            <div class="card border-success mb-3">
                                <div class="card-header bg-light fw-bold">
                                    <i class="bi bi-calculator me-2 text-success"></i>
                                    Resumen del Pago
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label text-muted">Cuotas seleccionadas</label>
                                            <div class="fs-5 fw-bold" id="cantidadCuotasSeleccionadas">
                                                0
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-muted">Total de cuotas seleccionado</label>
                                            <div class="fs-5 fw-bold text-primary">
                                                {{ $monedaPrestamo }} <span id="totalCuotasSeleccionadas">0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="montoEfectivo" class="form-label">Pago efectivo recibido</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Bs</span>
                                                <input type="number" step="0.01" class="form-control text-end @error('monto_efectivo') is-invalid @enderror" id="montoEfectivo" name="monto_efectivo" value="{{ old('monto_efectivo') }}" placeholder="0.00">
                                                @error('monto_efectivo')
                                                    <small class="text-danger">
                                                        {{ $message }}
                                                    </small>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-muted">Diferencia en efectivo</label>
                                            <div class="fs-5 fw-bold" id="contenedorDiferenciaPago">
                                                Bs <span id="diferenciaPago">0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fechaDeposito" class="form-label">Fecha de depósito</label>
                                            <input
                                                type="date"
                                                class="form-control @error('fecha_deposito') is-invalid @enderror"
                                                id="fechaDeposito"
                                                name="fecha_deposito"
                                                value="{{ old('fecha_deposito', now()->toDateString()) }}"
                                            >
                                            @error('fecha_deposito')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nop" class="form-label">NOP</label>
                                            <input
                                                type="text"
                                                class="form-control @error('nop') is-invalid @enderror"
                                                id="nop"
                                                name="nop"
                                                inputmode="numeric"
                                                maxlength="15"
                                                value="{{ old('nop') }}"
                                                placeholder="N.º de operación"
                                            >
                                            @error('nop')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        @if($esPrestamoDolares)
                                            <div class="col-md-4">
                                                <label class="form-label text-muted">T.C. aplicado en Solicitud </label>
                                                <input type="number" min="0.00001" step="0.00001" class="form-control text-end @error('tipo_cambio') is-invalid @enderror" id="tipoCambio" name="tipo_cambio"
                                                    value="{{ old('tipo_cambio', number_format($prestamo->tipo_cambio,5,'.','')) }}">
                                                @error('tipo_cambio')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        @endif

                                    </div>
                                    @if($esPrestamoDolares)
                                        <div class="alert alert-info mt-3 mb-0">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    Equivalente exigido: <strong>Bs <span id="totalExigidoBolivianos">0.00</span></strong>
                                                </div>
                                                <div class="col-md-4">
                                                    Pago efectivo convertido: <strong>$us <span id="montoEquivalenteDolares">0.00</span></strong>
                                                </div>
                                                <div class="col-md-4 small">
                                                    La conversión se actualiza en tiempo real con el tipo de cambio ingresado.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div id="mensajeResumenPago" class="d-none mt-3"></div>
                                     <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="glosaPago" class="form-label">Glosa</label>
                                            <textarea class="form-control @error('glosa') is-invalid @enderror" id="glosaPago" name="glosa" rows="2" placeholder="Detalle u observaciones del pago...">{{ old('glosa') }}</textarea>
                                            @error('glosa')
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                                    <input type="checkbox" class="form-check-input cuota-pendiente" name="cuotas[]" value="{{ $cuota->id }}" data-total="{{ $cuota->cuota_fija }}" @checked(in_array($cuota->id, old('cuotas', [])))>
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
                                @error('cuotas')
                                <div class="mt-2">
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                </div>
                            @enderror
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success" @disabled($cuotasPendientes->isEmpty())>
                                    <i class="bi bi-save me-1"></i>Guardar Pago
                                </button>
                            </div>
                            </form>
                            {{-- Aquí irá la tabla de cuotas --}}

                        </div>
                        <div class="tab-pane fade {{ $errors->pagoTotal->any() ? 'show active' : '' }}" id="total" role="tabpanel">
                            <form id="formPagoTotal" method="POST" action="{{ route('prestamos.pagos.total.store', $prestamo) }}"
                                data-moneda-prestamo="{{ $prestamo->tipo->tipo_moneda }}"
                                data-saldo-total="{{ number_format((float) $prestamo->saldo_actual, 2, '.', '') }}"
                                data-cuotas-pendientes="{{ $cantidadCuotasPendientes }}">
                                @csrf
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-light fw-bold">
                                        <i class="bi bi-wallet2 me-2 text-success"></i>
                                        Liquidación Total del Préstamo
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning">
                                            Este pago cancelará el saldo completo y marcará las
                                            {{ $cantidadCuotasPendientes }} cuotas pendientes como pagadas.
                                        </div>

                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Saldo total pendiente</label>
                                                <div class="fs-5 fw-bold text-primary">
                                                    {{ $monedaPrestamo }}
                                                    <span id="saldoTotalPrestamo">{{ number_format($prestamo->saldo_actual, 2) }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="montoEfectivoTotal" class="form-label">Pago efectivo recibido</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs</span>
                                                    <input type="number" min="0.01" step="0.01"
                                                        class="form-control text-end @error('monto_efectivo_total', 'pagoTotal') is-invalid @enderror"
                                                        id="montoEfectivoTotal" name="monto_efectivo_total"
                                                        value="{{ old('monto_efectivo_total') }}" placeholder="0.00" required>
                                                </div>
                                                @error('monto_efectivo_total', 'pagoTotal')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label for="fechaDepositoTotal" class="form-label">Fecha de depósito</label>
                                                <input
                                                    type="date"
                                                    class="form-control @error('fecha_deposito_total', 'pagoTotal') is-invalid @enderror"
                                                    id="fechaDepositoTotal"
                                                    name="fecha_deposito_total"
                                                    value="{{ old('fecha_deposito_total', now()->toDateString()) }}"
                                                >
                                                @error('fecha_deposito_total', 'pagoTotal')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label for="nopTotal" class="form-label">NOP</label>
                                                <input
                                                    type="text"
                                                    class="form-control @error('nop_total', 'pagoTotal') is-invalid @enderror"
                                                    id="nopTotal"
                                                    name="nop_total"
                                                    inputmode="numeric"
                                                    maxlength="15"
                                                    value="{{ old('nop_total') }}"
                                                    placeholder="N.º de operación"
                                                >
                                                @error('nop_total', 'pagoTotal')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            @if($esPrestamoDolares)
                                                <div class="col-md-3">
                                                    <label for="tipoCambioTotal" class="form-label text-muted">Tipo de cambio aplicado</label>
                                                    <input type="number" min="0.00001" step="0.00001"
                                                        class="form-control text-end @error('tipo_cambio_total', 'pagoTotal') is-invalid @enderror"
                                                        id="tipoCambioTotal" name="tipo_cambio_total"
                                                        value="{{ old('tipo_cambio_total', number_format($prestamo->tipo_cambio, 5, '.', '')) }}"
                                                        required>
                                                    @error('tipo_cambio_total', 'pagoTotal')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            @endif

                                            <div class="col-md-3">
                                                <label class="form-label text-muted">Diferencia en efectivo</label>
                                                <div class="fs-5 fw-bold text-muted" id="contenedorDiferenciaTotal">
                                                    Bs <span id="diferenciaPagoTotal">0.00</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($esPrestamoDolares)
                                            <div class="alert alert-info mt-3 mb-0">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-md-4">
                                                        Equivalente exigido:
                                                        <strong>Bs <span id="totalExigidoBolivianosTotal">0.00</span></strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        Pago efectivo convertido:
                                                        <strong>$us <span id="montoEquivalenteDolaresTotal">0.00</span></strong>
                                                    </div>
                                                    <div class="col-md-4 small">
                                                        La liquidación se recalcula en tiempo real con el tipo de cambio ingresado.
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="glosaPagoTotal" class="form-label">Glosa</label>
                                                <textarea class="form-control @error('glosa_total', 'pagoTotal') is-invalid @enderror"
                                                    id="glosaPagoTotal" name="glosa_total" rows="2"
                                                    placeholder="Detalle u observaciones de la liquidación total..." required>{{ old('glosa_total') }}</textarea>
                                                @error('glosa_total', 'pagoTotal')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success"
                                        @disabled($prestamo->estado !== 'AC' || (float) $prestamo->saldo_actual <= 0 || $cantidadCuotasPendientes === 0)>
                                        <i class="bi bi-check-circle me-1"></i>Guardar Pago Total
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="contenedorDetallePrestamo"></div>
</x-app-layout>
