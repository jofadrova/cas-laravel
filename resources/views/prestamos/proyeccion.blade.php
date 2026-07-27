<x-app-layout>
    <x-slot name="header">Proyección de Préstamos</x-slot>

    <div id="proyeccionPrestamo"
        data-url-calcular="{{ route('prestamos.proyeccion.calcular') }}"
        data-url-reporte="{{ route('prestamos.proyeccion.reporte') }}"
        data-url-tipo-cambio="{{ route('prestamos.tipo-cambio', ['fecha' => '__FECHA__']) }}">

        <div class="alert alert-info shadow-sm">
            <i class="bi bi-info-circle-fill me-2"></i>
            Esta herramienta es referencial, no está vinculada a un asociado y no registra ninguna solicitud.
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-calculator me-2"></i>Datos de la proyección
            </div>
            <div class="card-body">
                <form id="formProyeccionPrestamo" novalidate>
                    @csrf
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <label for="proyeccionTipo" class="form-label">Tipo de préstamo</label>
                            <select id="proyeccionTipo" name="tipo_prestamo" class="form-select">
                                <option value="">-- Seleccione --</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tasa }}"
                                        data-moneda="{{ $tipo->tipo_moneda }}"
                                        data-monto="{{ $tipo->monto_max }}"
                                        data-plazo="{{ $tipo->plazo_max }}"
                                        data-interes="{{ $tipo->porcentaje }}"
                                        data-garantes="{{ $tipo->garante }}">
                                        {{ $tipo->descripcion_tasa }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error="tipo_prestamo"></div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="proyeccionFecha" class="form-label">Fecha</label>
                            <input id="proyeccionFecha" name="fecha" type="date" class="form-control"
                                value="{{ now()->format('Y-m-d') }}">
                            <div class="invalid-feedback" data-error="fecha"></div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="proyeccionMonto" class="form-label">Monto</label>
                            <div class="input-group">
                                <span id="proyeccionMoneda" class="input-group-text">-</span>
                                <input id="proyeccionMonto" name="monto" type="number" step="0.01" class="form-control">
                            </div>
                            <small id="proyeccionMontoMax" class="text-muted"></small>
                            <div class="invalid-feedback" data-error="monto"></div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="proyeccionPlazo" class="form-label">Plazo (meses)</label>
                            <input id="proyeccionPlazo" name="plazo" type="number" class="form-control">
                            <small id="proyeccionPlazoMax" class="text-muted"></small>
                            <div class="invalid-feedback" data-error="plazo"></div>
                        </div>

                        <div id="grupoTipoCambio" class="col-lg-2 col-md-4">
                            <label for="proyeccionTipoCambio" class="form-label">Tipo de cambio</label>
                            <div class="input-group">
                                <input id="proyeccionTipoCambio" name="tipo_cambio" type="number"
                                    step="0.00001" class="form-control">
                                <button id="actualizarTipoCambioProyeccion" type="button"
                                    class="btn btn-outline-secondary" title="Consultar cotización oficial">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small id="mensajeTipoCambioProyeccion" class="text-muted"></small>
                            <div class="invalid-feedback" data-error="tipo_cambio"></div>
                        </div>
                    </div>

                    <div id="resumenTipoProyeccion" class="row g-3 mt-2 d-none">
                        <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Moneda</small><div id="datoMoneda" class="fw-bold"></div></div></div>
                        <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Tasa mensual</small><div id="datoInteres" class="fw-bold"></div></div></div>
                        <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Monto máximo</small><div id="datoMontoMax" class="fw-bold"></div></div></div>
                        <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Plazo máximo</small><div id="datoPlazoMax" class="fw-bold"></div></div></div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button id="calcularProyeccion" type="submit" class="btn btn-success">
                            <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                            <i class="bi bi-calculator me-1"></i>Calcular proyección
                        </button>
                        <button id="imprimirProyeccion" type="button" class="btn btn-primary" disabled>
                            <i class="bi bi-printer me-1"></i>Imprimir reporte
                        </button>
                         <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div id="resultadoProyeccion" class="d-none">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-clipboard-data me-2"></i>Resultado de la proyección
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4"><small class="text-muted">Capital</small><div id="resultadoCapital" class="fs-5 fw-bold text-primary"></div></div>
                        <div class="col-lg-2 col-md-4"><small class="text-muted">Cuota estimada</small><div id="resultadoCuota" class="fs-5 fw-bold text-success"></div></div>
                        <div class="col-lg-2 col-md-4"><small class="text-muted">Interés total</small><div id="resultadoInteres" class="fs-5 fw-bold"></div></div>
                        <div class="col-lg-2 col-md-4"><small class="text-muted">ITF total</small><div id="resultadoItf" class="fs-5 fw-bold"></div></div>
                        <div class="col-lg-2 col-md-4"><small class="text-muted">Otros cargos</small><div id="resultadoCargos" class="fs-5 fw-bold"></div></div>
                        <div class="col-lg-2 col-md-4"><small class="text-muted">Total a pagar</small><div id="resultadoTotal" class="fs-5 fw-bold text-danger"></div></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-table me-2"></i>Cronograma proyectado
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>Fecha</th>
                                    <th class="text-end">Capital</th><th class="text-end">Interés</th>
                                    <th class="text-end">Min. Defensa</th><th class="text-end">ITF</th>
                                    <th class="text-end">Interés días</th><th class="text-end">Reposición</th>
                                    <th class="text-end">Cuota</th><th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="cronogramaProyeccion"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <form id="formReporteProyeccion" method="POST" target="_blank" action="{{ route('prestamos.proyeccion.reporte') }}" class="d-none">
            @csrf
            <input name="tipo_prestamo"><input name="fecha"><input name="monto">
            <input name="plazo"><input name="tipo_cambio">
        </form>
    </div>
</x-app-layout>
