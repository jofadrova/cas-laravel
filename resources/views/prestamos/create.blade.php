<x-app-layout>
<x-slot name="header">Nueva Solicitud de Préstamo</x-slot>
<form id="frmPrestamo" method="POST" action="{{ route('prestamos.store') }}">
    @csrf
<input type="hidden" id="urlValidarSolicitud" value="{{ route('prestamos.validarSolicitud') }}">
<input type="hidden" id="urlSimular" value="{{ route('prestamos.simular') }}">
<input type="hidden" id="cronograma" name="cronograma">
<div class="row g-4">
    <div class="col-xl-8">
        {{-- Datos del préstamo --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Datos del préstamo
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de préstamo</label>
                        <select id="tipoPrestamo" name="tipo_prestamo" class="form-select @error('tipo_prestamo') is-invalid @enderror">
                            <option value="">-- Seleccione --</option>
                            @foreach($tipos as $tipo)
                                <option
                                    value="{{ $tipo->id_tasa }}"
                                    @selected(old('tipo_prestamo') == $tipo->id_tasa)
                                    data-garante="{{ $tipo->garante }}"
                                    data-plazo="{{ $tipo->plazo_max }}"
                                    data-monto="{{ $tipo->monto_max }}"
                                    data-interes="{{ $tipo->porcentaje }}"
                                    data-moneda="{{ $tipo->tipo_moneda }}"
                                    data-mindefensa="{{ $tipo->min_defensa }}"
                                    data-itf="{{ $tipo->itf }}"
                                    data-papeleria="{{ $tipo->papeleria }}">
                                    {{ $tipo->descripcion_tasa }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_prestamo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <x-scas.papeleta-search name="id_socio" label="Solicitante" :value="old('id_socio')" />
                    </div>
                     <div class="col-md-3">
                        <label class="form-label">Fecha Depósito</label>
                        <input type="date" id="fechaPrestamo" name="fechaPrestamo" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fechaPrestamo', now()->format('Y-m-d')) }}">
                        @error('fechaPrestamo') <div class="invalid-feedback"> {{ $message }}</div>@enderror
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
                <input id="monto" name="monto" type="number" step="0.01" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto') }}">
                <small id="maxMonto" class="text-muted d-block mt-2"></small>
                @error('monto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
             <div class="col-md-3">
                <label class="form-label">Tipo de Cambio</label>
                <div class="input-group">
                    <input type="number" step="0.00001" min="0" class="form-control @error('tipo_cambio') is-invalid @enderror"
                        id="tipo_cambio"
                        name="tipo_cambio"
                        value="{{ old('tipo_cambio') }}">
                    <button type="button" class="btn btn-outline-secondary" id="btnActualizarTipoCambio" title="Consultar cotización oficial">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    @error('tipo_cambio') <div class="invalid-feedback"> {{ $message }} </div>@enderror
                </div>
                <small id="mensajeTipoCambio" class="form-text text-muted"> Seleccione una fecha para obtener la cotización oficial.</small>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Plazo (meses)</label>
                <input id="plazo" name="plazo" type="number" class="form-control @error('plazo') is-invalid @enderror" value="{{ old('plazo') }}">
                <small id="maxPlazo" class="text-muted d-block mt-2"></small>
                @error('plazo') <div class="invalid-feedback"> {{ $message }}</div>@enderror
            </div>
            <div class="col-lg-3">
                <label class="form-label">Nro. Asiento</label>
                <div class="input-group">
                    <span class="input-group-text">
                        EG-
                    </span>
                    <input id="asiento" name="asiento" class="form-control @error('asiento') is-invalid @enderror" value="{{ old('asiento') }}">
                    @error('asiento') <div class="invalid-feedback"> {{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Motivo</label>
                <textarea name="motivo" rows="3" class="form-control @error('motivo') is-invalid @enderror" style="resize:none" maxlength="200" >{{ old('motivo') }}</textarea>
                 @error('motivo') <div class="invalid-feedback"> {{ $message }}</div>@enderror
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
    <button
        type="button"
        class="btn btn-success"
        data-bs-toggle="modal"
        data-bs-target="#modalConfirmarConsolidacion">
        <i class="bi bi-check-circle"></i>
        Consolidar y Guardar Prestamo
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
<div class="modal fade" id="modalConfirmarConsolidacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>
                    Confirmar consolidación
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p class="mb-3">
                    ¿Desea consolidar y guardar este préstamo?
                </p>

                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    El préstamo será registrado de forma definitiva y se
                    generará el cronograma de pagos correspondiente.
                    <strong>Esta acción no puede deshacerse.</strong>
                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-success"
                        id="btnConfirmarConsolidacion">
                    <i class="bi bi-check-circle me-1"></i>
                    Consolidar
                </button>

            </div>

        </div>
    </div>
</div>
<script>
document.getElementById('btnConfirmarConsolidacion').addEventListener('click', function () {
    document.getElementById('frmPrestamo').submit();
});
const rutaTipoCambio = "{{ route('prestamos.tipo-cambio', ['fecha' => '__FECHA__']) }}";
const fechaPrestamo = document.getElementById('fechaPrestamo');
const tipoCambio = document.getElementById('tipo_cambio');
const mensaje = document.getElementById('mensajeTipoCambio');
const btnActualizar = document.getElementById('btnActualizarTipoCambio');

async function cargarTipoCambio() {

    const fecha = fechaPrestamo.value;

    if (!fecha) {
        return;
    }

    btnActualizar.disabled = true;

    try {

        const url = rutaTipoCambio.replace('__FECHA__', fecha);

        const response = await fetch(url);

        const data = await response.json();

        if (data.ok) {

            tipoCambio.value = Number(data.tipo_cambio).toFixed(2);
            tipoCambio.classList.remove('is-invalid');
            tipoCambio.classList.add('is-valid');
            mensaje.className = 'form-text text-success';

            mensaje.innerHTML =
                '<i class="bi bi-check-circle-fill me-1"></i>' +
                'Cotización oficial cargada correctamente.';

        } else {
            tipoCambio.value = "";
            tipoCambio.classList.remove('is-valid');
            tipoCambio.classList.remove('is-invalid');
            tipoCambio.setCustomValidity('');
            mensaje.className = 'form-text text-warning';
            mensaje.innerHTML =
                '<i class="bi bi-exclamation-triangle-fill me-1"></i>' +
                'No existe una cotización registrada para la fecha seleccionada. Puede ingresar el tipo de cambio manualmente.';

        }

    } catch (e) {

        mensaje.className = 'form-text text-danger';

        mensaje.innerHTML =
            '<i class="bi bi-x-circle-fill me-1"></i>' +
            'No fue posible obtener la cotización.';

    } finally {

        btnActualizar.disabled = false;

    }

}
fechaPrestamo.addEventListener('change', cargarTipoCambio);
btnActualizar.addEventListener('click', cargarTipoCambio);
tipoCambio.addEventListener('input', function () {

    tipoCambio.classList.remove('is-valid');
    tipoCambio.classList.remove('is-invalid');

    mensaje.className = 'form-text text-primary';

    mensaje.innerHTML =
        '<i class="bi bi-pencil-square me-1"></i>' +
        'Tipo de cambio modificado manualmente.';

});
</script>
