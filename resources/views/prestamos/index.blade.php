<x-app-layout>
    <x-slot name="header">Gestión de Préstamos</x-slot>
    {{-- Mensaje --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- Buscador --}}
    <form method="GET">
        <div class="row mb-3 align-items-end">
            {{-- Buscar por --}}
            <div class="col-md-2">
                <label class="form-label">Buscar por</label>
                <select name="campo" class="form-select">
                    <option value="solicitud"
                        {{ request('campo', 'solicitud') == 'solicitud' ? 'selected' : '' }}>
                        Nro. Solicitud
                    </option>
                    <option value="papeleta"
                        {{ request('campo') == 'papeleta' ? 'selected' : '' }}>
                        Nro. Papeleta
                    </option>
                    <option value="asociado"
                        {{ request('campo') == 'asociado' ? 'selected' : '' }}>
                        Asociado
                    </option>
                </select>
            </div>
            {{-- Criterio --}}
            <div class="col-md-3">
                <label class="form-label">Criterio</label>
                <input type="text" name="buscar" class="form-control" placeholder="Ingrese el criterio..." value="{{ request('buscar') }}">
            </div>
            {{-- Estado --}}
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="AC"
                        {{ request('estado') == 'AC' ? 'selected' : '' }}>
                        Activo
                    </option>
                    <option value="IN"
                        {{ request('estado') == 'IN' ? 'selected' : '' }}>
                        Inactivo
                    </option>
                    <option value="PA"
                        {{ request('estado') == 'PA' ? 'selected' : '' }}>
                        Cancelado
                    </option>
                    <option value="CE"
                        {{ request('estado') == 'CE' ? 'selected' : '' }}>
                        Cerrado por refinanciamiento
                    </option>
                </select>
            </div>
            {{-- Tipo --}}
            <div class="col-md-3">
                <label class="form-label">Tipo de préstamo</label>
                <select name="tipo_prestamo" class="form-select">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                        <option
                            value="{{ $tipo->id_tasa }}"
                            {{ request('tipo_prestamo') == $tipo->id_tasa ? 'selected' : '' }}>
                            {{ $tipo->descripcion_tasa }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Botones --}}
            <div class="col-md-2 d-grid gap-2">
                <button class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                    Limpiar
                </a>
            </div>
        </div>
    </form>
    {{-- Card Principal --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-cash-coin me-2"></i>
                Gestión de Préstamos
            </h5>
            <a href="{{ route('prestamos.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Préstamo
            </a>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Solicitud</th>
                        <th class="text-center">Nro. Papeleta</th>
                        <th>Socio</th>
                        <th>Tipo</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Cuotas</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestamos as $prestamo)
                        @php
                            $prestamoCerrado = in_array($prestamo->estado, ['PA', 'CE'], true);
                            $tieneCuotasPagadas = (int) $prestamo->cuotas_pagadas_count > 0;
                            $puedeRefinanciar = $prestamo->estado === 'AC'
                                && (int) optional($prestamo->tipo)->id_tasa === 1;
                            $puedeReprogramar = $prestamo->estado === 'AC'
                                && (float) $prestamo->saldo_actual > 0
                                && (int) $prestamo->cuotas_pendientes_count > 0
                                && (int) $prestamo->periodo < (int) optional($prestamo->tipo)->plazo_max;
                        @endphp
                        <tr>
                            <td>{{ $prestamo->nro_solicitud }}</td>
                            <td class="text-center">{{ $prestamo->socio->institucion->papeleta ?? '-' }}</td>
                            <td>{{ implode(' ', array_filter([
                                    optional($prestamo->socio)->paterno,
                                    optional($prestamo->socio)->materno,
                                    optional($prestamo->socio)->nombres,
                                ])) }}
                            </td>
                            <td>{{ optional($prestamo->tipo)->descripcion_tasa }}</td>
                            <td class="text-end">{{ number_format($prestamo->monto,2) }}</td>
                            <td class="text-center">{{ $prestamo->ultima_cuota }}/{{ $prestamo->periodo }}</td>
                            <td class="text-end">{{ number_format($prestamo->saldo_actual,2) }}</td>
                            <td class="text-center">
                                @if($prestamo->estado=='AC')
                                    <span class="badge bg-success">ACTIVO</span>
                                @elseif($prestamo->estado === 'PA')
                                    <span class="badge bg-info">CANCELADO</span>
                                @elseif($prestamo->estado === 'CE')
                                    <span class="badge bg-dark">CERRADO - REFINANCIADO</span>
                                @else
                                    <span class="badge bg-secondary">{{ $prestamo->estado }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill me-1"></i>
                                        Acciones
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <h6 class="dropdown-header text-uppercase text-muted fw-bold">Consulta</h6>
                                        </li>
                                        <li>
                                            <a href="{{ route('prestamos.detalle', $prestamo) }}" class="dropdown-item btn-detalle">
                                                <i class="bi bi-eye me-2"></i>
                                                Ver detalle
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('prestamos.reporte',$prestamo) }}" target="_blank">
                                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>
                                                Cronograma PDF
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <h6 class="dropdown-header">MANTENIMIENTO</h6>
                                        </li>
                                        @if(!$prestamoCerrado && !$tieneCuotasPagadas && $prestamo->editable)
                                        <li>
                                            <a class="dropdown-item"
                                            href="{{ route('prestamos.edit',$prestamo) }}">
                                                <i class="bi bi-pencil-square me-2 text-warning"></i>
                                                Editar préstamo
                                            </a>
                                        </li>
                                        @else
                                        <li>
                                            <span class="dropdown-item disabled text-muted"
                                                @if($tieneCuotasPagadas)
                                                    title="No se puede editar porque el préstamo tiene cuotas pagadas"
                                                @endif>
                                                <i class="bi bi-lock-fill me-2 text-secondary"></i>
                                                Editar préstamo
                                            </span>
                                        </li>
                                        @endif
                                        @if($prestamo->editable)
                                        <li>
                                            <form method="POST"
                                                action="{{ route('prestamos.bloquear-edicion', $prestamo) }}"
                                                data-confirm-title="Confirmar bloqueo de edición"
                                                data-confirm-message="Se bloqueará la edición del préstamo N.º {{ $prestamo->nro_solicitud }}."
                                                data-confirm-button="Bloquear edición">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $prestamoCerrado ? 'disabled' : '' }}"
                                                    @disabled($prestamoCerrado)>
                                                    <i class="bi bi-lock-fill me-2 text-danger"></i>
                                                    Bloquear edición
                                                </button>
                                            </form>
                                        </li>
                                        @else
                                        <li>
                                            <form method="POST"
                                                action="{{ route('prestamos.habilitar-edicion', $prestamo) }}"
                                                data-confirm-title="Confirmar habilitación de edición"
                                                data-confirm-message="Se habilitará nuevamente la edición del préstamo N.º {{ $prestamo->nro_solicitud }}."
                                                data-confirm-button="Habilitar edición">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $prestamoCerrado ? 'disabled' : '' }}"
                                                    @disabled($prestamoCerrado)>
                                                    <i class="bi bi-unlock-fill me-2 text-success"></i>
                                                    Habilitar edición
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        <li>
                                            @if(!$prestamoCerrado && $prestamo->tipo?->id_tasa == 1)
                                                <a class="dropdown-item" href="{{ route('prestamos.garantes', $prestamo) }}">
                                                    <i class="bi bi-people me-2"></i>
                                                    Cambio de garantes
                                                </a>
                                            @else
                                                <span class="dropdown-item disabled text-muted">
                                                    <i class="bi bi-people me-2"></i>
                                                    Cambio de garantes
                                                </span>
                                            @endif
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <h6 class="dropdown-header">OPERACIONES</h6>
                                        </li>
                                        <li>
                                            @if($prestamoCerrado)
                                                <span class="dropdown-item disabled text-muted">
                                                    <i class="bi bi-cash-coin me-2"></i>
                                                    Registrar pago
                                                </span>
                                            @else
                                                <a href="{{ route('prestamos.pagos', $prestamo) }}" class="dropdown-item">
                                                    <i class="bi bi-cash-coin me-2"></i>
                                                    Registrar pago
                                                </a>
                                            @endif
                                        </li>
                                        <li>
                                            <a class="dropdown-item btn-reporte-pagos"
                                            href="{{ route('prestamos.pagos.reporte', $prestamo) }}">
                                                <i class="bi bi-receipt me-2 text-info"></i>
                                                Reporte de pagos
                                            </a>
                                        </li>
                                        <li>
                                            @if($prestamoCerrado)
                                                <span class="dropdown-item disabled text-muted">
                                                    <i class="bi bi-arrow-down-circle me-2 text-warning"></i>
                                                    Amortización de capital
                                                </span>
                                            @else
                                                <a class="dropdown-item"
                                                    href="{{ route('prestamos.amortizacion-capital', $prestamo) }}">
                                                    <i class="bi bi-arrow-down-circle me-2 text-warning"></i>
                                                    Amortización de capital
                                                </a>
                                            @endif
                                        </li>
                                        <li>
                                            @if($puedeRefinanciar)
                                                <a class="dropdown-item"
                                                    href="{{ route('prestamos.refinanciamiento', $prestamo) }}">
                                                    <i class="bi bi-arrow-repeat me-2 text-warning"></i>
                                                    Refinanciar
                                                </a>
                                            @else
                                                <span class="dropdown-item disabled text-muted">
                                                    <i class="bi bi-arrow-repeat me-2 text-warning"></i>
                                                    Refinanciar
                                                </span>
                                            @endif
                                        </li>
                                        <li>
                                            @if($puedeReprogramar)
                                                <a class="dropdown-item"
                                                    href="{{ route('prestamos.reprogramacion', $prestamo) }}">
                                                    <i class="bi bi-calendar2-range me-2 text-primary"></i>
                                                    Reprogramar préstamo
                                                </a>
                                            @else
                                                <span class="dropdown-item disabled text-muted">
                                                    <i class="bi bi-calendar2-range me-2 text-secondary"></i>
                                                    Reprogramar préstamo
                                                </span>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No existen préstamos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        {{ $prestamos->links() }}
        </div>
    </div>
    <div id="contenedorDetallePrestamo"></div>
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const campo = document.querySelector('select[name="campo"]');
    const buscar = document.querySelector('input[name="buscar"]');

    function actualizarPlaceholder() {
        switch (campo.value) {
            case 'solicitud':
                buscar.placeholder = 'Ej.: 10548';
                break;

            case 'papeleta':
                buscar.placeholder = 'Ej.: 000022125';
                break;

            case 'asociado':
                buscar.placeholder = 'Nombre o apellido del asociado';
                break;

            default:
                buscar.placeholder = 'Ingrese el criterio...';
        }
    }
    actualizarPlaceholder();
    campo.addEventListener('change', actualizarPlaceholder);

});

document.addEventListener('click', async function (e) {
    const boton = e.target.closest('.btn-detalle');
    if (!boton) return;
    e.preventDefault();
    const id = boton.dataset.id;
    try {
        const response = await fetch(boton.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok)
            throw new Error();
        const html = await response.text();
// Aquí va
const contenedor = document.getElementById('contenedorDetallePrestamo');
contenedor.innerHTML = '';
contenedor.innerHTML = html;
// Ahora el modal ya existe en el DOM
const modal = new bootstrap.Modal(
    document.getElementById('modalDetallePrestamo')
);
modal.show();
    } catch (error) {
        console.error(error);
    }
});

document.addEventListener('click', async function (e) {
    const boton = e.target.closest('.btn-reporte-pagos');

    if (!boton) return;

    e.preventDefault();

    const contenedor = document.getElementById('contenedorDetallePrestamo');
    contenedor.innerHTML = `
        <div class="modal fade" id="modalReportePagos" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-receipt me-2"></i>
                            Reporte de Pagos
                        </h5>
                    </div>
                    <div class="modal-body d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="spinner-border text-success mb-3" role="status" aria-hidden="true"></div>
                        <div class="fw-semibold text-muted">Cargando reporte de pagos...</div>
                    </div>
                </div>
            </div>
        </div>`;

    const modalElemento = document.getElementById('modalReportePagos');
    const modal = new bootstrap.Modal(modalElemento, {
        backdrop: 'static',
        keyboard: false
    });

    modalElemento.addEventListener('hidden.bs.modal', () => {
        contenedor.innerHTML = '';
    }, { once: true });

    modal.show();

    try {
        const response = await fetch(boton.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error();

        const respuesta = document.createElement('div');
        respuesta.innerHTML = await response.text();
        const contenidoReporte = respuesta.querySelector('.modal-content');

        if (!contenidoReporte) throw new Error();

        modalElemento
            .querySelector('.modal-content')
            .replaceWith(contenidoReporte);
    } catch (error) {
        console.error(error);
        modalElemento.querySelector('.modal-body').innerHTML = `
            <div class="alert alert-danger mb-0">
                No fue posible cargar el reporte de pagos. Intente nuevamente.
            </div>`;
        modalElemento.querySelector('.modal-header').insertAdjacentHTML(
            'beforeend',
            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>'
        );
    }
});
</script>
    @endpush
</x-app-layout>
