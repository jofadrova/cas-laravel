<x-app-layout>
    <x-slot name="header">Amortización de Capital</x-slot>

    @php
        $esPrestamoDolares = $prestamo->tipo->tipo_moneda === 'SU';
        $monedaPrestamo = $esPrestamoDolares ? '$us' : 'Bs';
        $otrosCargosPrimeraCuota =
            (float) $primeraCuota->itf
            + (float) $primeraCuota->papel
            + (float) $primeraCuota->comision_mindef
            + (float) $primeraCuota->rep_formulario;
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
                    <label class="text-muted small">Estado</label>
                    <div><span class="badge bg-success">ACTIVO</span></div>
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
                <div class="col-md-4">
                    <label class="text-muted small">Monto original</label>
                    <div class="fw-bold text-primary">
                        {{ $monedaPrestamo }} {{ number_format($prestamo->monto, 2) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Saldo capital actual</label>
                    <div class="fw-bold text-danger">
                        {{ $monedaPrestamo }} {{ number_format($prestamo->saldo_actual, 2) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small">Cuotas restantes</label>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-semibold">{{ $cuotasPendientes->count() }}</span>
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

    <form id="formAmortizacionCapital" method="POST"
        action="{{ route('prestamos.amortizacion-capital.store', $prestamo) }}"
        novalidate
        data-es-dolares="{{ $esPrestamoDolares ? '1' : '0' }}"
        data-saldo="{{ number_format((float) $prestamo->saldo_actual, 2, '.', '') }}"
        data-tasa-mensual="{{ number_format((float) $prestamo->interes / 100, 8, '.', '') }}"
        data-min-defensa="{{ number_format((float) $prestamo->min_defensa / 100, 8, '.', '') }}"
        data-cuotas-restantes="{{ $cuotasPendientes->count() }}"
        data-cuota-total-actual="{{ number_format((float) $primeraCuota->cuota_fija, 2, '.', '') }}"
        data-otros-cargos="{{ number_format($otrosCargosPrimeraCuota, 2, '.', '') }}"
        data-primer-numero-cuota="{{ $primeraCuota->nro_cuota }}"
        data-periodo-actual="{{ $prestamo->periodo }}">
        @csrf
        <script type="application/json" id="cargosCuotasPendientes">
            @json($cuotasPendientes->map(fn ($cuota) =>
                round(
                    (float) $cuota->min_defensa
                    + (float) $cuota->itf
                    + (float) $cuota->papel
                    + (float) $cuota->comision_mindef
                    + (float) $cuota->rep_formulario,
                    2
                )
            )->values())
        </script>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-arrow-down-circle me-2"></i>
                Amortización de Capital
            </div>
            <div class="card-body">
                <div id="mensajeTipoRecalculo" class="alert alert-info">
                    La amortización reducirá la cuota mensual desde el próximo periodo y mantendrá el plazo.
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Saldo capital actual</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $monedaPrestamo }}</span>
                            <input type="text" class="form-control text-end"
                                value="{{ number_format($prestamo->saldo_actual, 2, '.', '') }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label d-block">Tipo de recálculo</label>
                        <div class="d-flex flex-wrap gap-4 pt-2">
                            <div class="form-check">
                                <input class="form-check-input tipo-recalculo" type="radio"
                                    name="tipo_recalculo" id="reducirCuota" value="CUOTA"
                                    @checked(old('tipo_recalculo', 'CUOTA') === 'CUOTA')>
                                <label class="form-check-label" for="reducirCuota">
                                    Reducir cuota
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input tipo-recalculo" type="radio"
                                    name="tipo_recalculo" id="reducirPlazo" value="PLAZO"
                                    @checked(old('tipo_recalculo') === 'PLAZO')>
                                <label class="form-check-label" for="reducirPlazo">
                                    Reducir tiempo del préstamo
                                </label>
                            </div>
                        </div>
                        @error('tipo_recalculo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="montoEfectivoAmortizacion" class="form-label">
                            {{ $esPrestamoDolares ? 'Monto efectivo a amortizar' : 'Monto a capital' }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Bs</span>
                            <input type="number" step="0.01"
                                class="form-control text-end @error('monto_efectivo') is-invalid @enderror"
                                id="montoEfectivoAmortizacion" name="monto_efectivo"
                                value="{{ old('monto_efectivo') }}" placeholder="0.00">
                        </div>
                        @error('monto_efectivo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    @if($esPrestamoDolares)
                        <div class="col-md-4">
                            <label for="tipoCambioAmortizacion" class="form-label">Tipo de cambio</label>
                            <input type="number" step="0.00001"
                                class="form-control text-end @error('tipo_cambio') is-invalid @enderror"
                                id="tipoCambioAmortizacion" name="tipo_cambio"
                                value="{{ old('tipo_cambio', number_format($prestamo->tipo_cambio, 5, '.', '')) }}">
                            @error('tipo_cambio')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monto amortizado en dólares</label>
                            <div class="input-group">
                                <span class="input-group-text">$us</span>
                                <input type="text" class="form-control text-end"
                                    id="montoCapitalConvertido" value="0.00" readonly>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="autorizacionCartera" class="form-label">Autorización Cartera</label>
                        <textarea class="form-control @error('autorizacion') is-invalid @enderror"
                            id="autorizacionCartera" name="autorizacion" rows="3"
                            >{{ old('autorizacion') }}</textarea>
                        @error('autorizacion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="observacionesAmortizacion" class="form-label">Observaciones / Anexo</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror"
                            id="observacionesAmortizacion" name="observaciones" rows="3"
                            >{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <hr>

                <div id="resultadoAmortizacion" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nueva cuota estimada</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $monedaPrestamo }}</span>
                            <input type="text" class="form-control text-end"
                                id="nuevaCuotaEstimada" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nuevo plazo estimado</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-end"
                                id="nuevoPlazoEstimado" readonly>
                            <span class="input-group-text">cuotas totales</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nuevo saldo capital</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $monedaPrestamo }}</span>
                            <input type="text" class="form-control text-end"
                                id="nuevoSaldoCapital" readonly>
                        </div>
                    </div>
                </div>

                <div id="errorSimulacionAmortizacion" class="alert alert-danger d-none mt-3 mb-0"></div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </a>
                <button type="button" class="btn btn-outline-primary" id="btnSimularAmortizacion">
                    <i class="bi bi-calculator me-1"></i>Simular nueva cuota
                </button>
                <button type="submit" class="btn btn-success" id="btnAplicarAmortizacion">
                    <i class="bi bi-check-circle me-1"></i>Aplicar amortización
                </button>
            </div>
        </div>
    </form>

    <div id="contenedorDetallePrestamo"></div>
</x-app-layout>
