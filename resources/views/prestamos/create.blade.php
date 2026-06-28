<x-app-layout>
<x-slot name="header">Nueva Solicitud de Préstamo</x-slot>
<form id="frmPrestamo" method="POST" action="{{ route('prestamos.store') }}">
    @csrf
<input type="hidden" id="urlValidarSolicitud" value="{{ route('prestamos.validarSolicitud') }}">
<input type="hidden" id="urlSimular" value="{{ route('prestamos.simular') }}">
<div class="row g-4"> 
    <div class="col-xl-8">
        {{-- Datos del préstamo --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Datos del préstamo
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de préstamo</label>
                        <select id="tipoPrestamo" name="tipo_prestamo" class="form-select">
                            <option value="">-- Seleccione --</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id_tasa }}"
                                    data-garante="{{ $tipo->garante }}"
                                    data-plazo="{{ $tipo->plazo_max }}"
                                    data-monto="{{ $tipo->monto_max }}"
                                    data-interes="{{ $tipo->porcentaje }}"
                                    data-moneda="{{ $tipo->tipo_moneda }}"
                                    data-mindefensa="{{ $tipo->min_defensa }}"
                                    data-itf="{{ $tipo->itf }}">                                    
                                    {{ $tipo->descripcion_tasa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <x-scas.papeleta-search name="id_socio" label="Solicitante" required="true"/>
                    </div>
                </div>
            </div>
        </div>
        {{-- Garantes --}}
        <div id="cardGarantes" class="card shadow-sm mb-4 d-none">
            <div class="card-header">Garantes</div>
            <div class="card-body">
                <div id="garantesContainer" class="row g-4">
                </div>
            </div>
        </div>
        {{-- Datos financieros --}}
        <div class="card shadow-sm mb-4">
    <div class="card-header">
        Datos financieros
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-3">
                <label class="form-label">Monto</label>
                <input id="monto" name="monto" type="number" step="0.01" class="form-control">
                <small id="maxMonto" class="text-muted d-block mt-2"></small>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Plazo (meses)</label>
                <input id="plazo" name="plazo" type="number" class="form-control">
                <small id="maxPlazo" class="text-muted d-block mt-2"></small>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Nro. Asiento</label>
                <div class="input-group">
                    <span class="input-group-text">
                        EG-
                    </span>
                    <input id="asiento" name="asiento" class="form-control">
                </div>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Fecha préstamo</label>
                <input type="date" id="fechaPrestamo" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Motivo</label>
                <textarea name="motivo" rows="3" class="form-control"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mt-4">
    <div class="card-header">Cronograma de pagos</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th class="text-end">Capital</th>
                        <th class="text-end">Interés</th>
                        <th class="text-end">Cuota</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody id="cronogramaBody">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Complete los datos del préstamo...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div><br>
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-success"><i class="fas fa-floppy-disk me-1"></i>
        Guardar y Consolidar el Préstamo
    </button>
    <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Cancelar
    </a>
</div>
</div>

    <!-- =======================
          RESUMEN
    ======================== -->
    <div class="col-xl-4">
        <div class="card shadow sticky-top" style="top:90px;">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-data me-2"></i>
                Resumen del préstamo
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <strong>Estado</strong>
                        <span id="rEstado" class="badge bg-secondary">
                            En captura
                        </span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Tipo</small>
                    <div id="rTipo"class="fw-bold">
                        -
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Moneda</small>
                    <div id="rMoneda" class="fw-bold">
                        -
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Interés</small>
                    <div id="rInteres"class="fw-bold">
                        -
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Garantes</small>
                    <div id="rGarantes" class="fw-bold">
                        -
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Monto máximo</small>
                    <h5 id="rMontoMax" class="text-success mt-1">
                        -
                    </h5>
                </div>
                <div>
                    <small class="text-muted">Plazo máximo</small>
                    <h5 id="rPlazoMax"class="text-primary mt-1">
                        -
                    </h5>
                </div>
                <hr>
                <small class="text-muted">Solicitante</small>
                <div id="rSolicitante">
                    <span class="text-muted">Sin seleccionar</span>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">Monto solicitado</small>
                    <div id="rMonto" class="fw-bold">
                        -
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Plazo solicitado</small>
                    <div id="rPlazo" class="fw-bold">
                        -
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Garantes registrados</small>
                    <div id="rGarantesActuales" class="fw-bold">
                        0
                    </div>
                </div>
                <hr>                
                <div class="mb-2">
                    <small class="text-muted">ITF</small>
                    <div id="rItf" class="fw-bold">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Papelería</small>
                    <div id="rPapeleria" class="fw-bold">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Monto líquido</small>
                    <div id="rLiquido" class="fw-bold text-success">-</div>
                </div>
                <div>
                    <small class="text-muted">Cuota mensual estimada</small>
                    <div id="rCuota" class="fw-bold">-</div>
                </div>
                <div>
                    <small class="text-muted">Interés total</small>
                    <div id="rInteresCalculado" class="fw-bold">-</div>
                </div>
                <div>
                    <small class="text-muted">Total a pagar</small>
                    <div id="rTotalPagado" class="fw-bold">0.00</div>
                </div>               
            </div>
        </div>
    </div>
</div>
</form>
</x-app-layout>