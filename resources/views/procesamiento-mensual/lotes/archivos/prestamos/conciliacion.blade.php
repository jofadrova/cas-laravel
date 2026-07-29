<x-app-layout>
    <x-slot name="header">
        Conciliación de préstamos · {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Comparación de préstamos</h4>
                <div class="text-muted">
                    {{ $lote->periodo }}
                    <span class="mx-1">·</span>
                    El lote permanece en
                    <span class="badge {{ $lote->clase_estado }}">
                        {{ $lote->estado }}
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form id="formRecompararPrestamos" method="POST" action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.comparar', $lote) }}" >
                    @csrf
                    <button type="submit" id="btnRecompararPrestamos" class="btn btn-success">
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Volver a comparar
                    </button>
                </form>
                <a href="{{ route('procesamiento-mensual.lotes.archivos.index', $lote) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver a archivos
                </a>
            </div>
        </div>

        <div class="alert {{ $integridadCompleta ? 'alert-success' : 'alert-danger' }}">
            <div class="d-flex align-items-center gap-2">
                <i class="bi {{ $integridadCompleta ? 'bi-shield-check' : 'bi-exclamation-octagon-fill' }} fs-4"></i>
                <div>
                    <div class="fw-semibold">
                        Control de integridad:
                        {{ number_format($totalImportados) }} importados =
                        {{ number_format($totalRegistrosAtendidos) }} atendidos
                    </div>
                    <div class="small">
                        {{ $integridadCompleta
                            ? number_format($totalOperacionesClasificadas)
                                . ' operaciones de préstamo fueron clasificadas individualmente.'
                            : 'La conciliación está incompleta y debe ejecutarse nuevamente.' }}
                    </div>
                </div>
            </div>
        </div>

        @php
            $tarjetas = [
                'COINCIDE' => ['COINCIDE', 'bg-success', 'bi-check-circle-fill'],
                'FALTA' => ['FALTA', 'bg-danger', 'bi-dash-circle-fill'],
                'DEMASIA' => ['DEMASÍA', 'bg-warning text-dark', 'bi-plus-circle-fill'],
                'SOCIO_NO_ENCONTRADO' => ['SOCIO NO ENCONTRADO', 'bg-dark', 'bi-person-x-fill'],
                'SIN_CUOTA' => ['SIN CUOTA', 'bg-secondary', 'bi-calendar-x-fill'],
                'TIPO_NO_CLASIFICADO' => ['TIPO NO CLASIFICADO', 'bg-info text-dark', 'bi-question-circle-fill'],
            ];
        @endphp

        <div class="row g-3 mb-4">
            @foreach($tarjetas as $estado => [$etiqueta, $clase, $icono])
                <div class="col-sm-6 col-xl">
                    <a href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                            array_filter([
                                'lote' => $lote,
                                'clasificacion' => $estado,
                                'papeleta' => $papeletaBuscada,
                                'nombre' => $nombreBuscado,
                            ])
                        ) }}"
                        class="text-decoration-none" >
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <div class="text-muted small">
                                            {{ $etiqueta }}
                                        </div>
                                        <div class="fs-4 fw-bold text-dark">
                                            {{ number_format($resumen[$estado]) }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $clase }} align-self-start">
                                        <i class="bi {{ $icono }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6 col-xl-3">
                        <label for="clasificacion" class="form-label fw-semibold">
                            Clasificación
                        </label>
                        <select id="clasificacion" name="clasificacion" class="form-select" >
                            <option value="">Todas</option>
                            @foreach($clasificaciones as $estado)
                                <option value="{{ $estado }}" @selected($clasificacionSeleccionada === $estado)>
                                    {{ $tarjetas[$estado][0] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label for="papeleta" class="form-label fw-semibold">
                            Papeleta
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-hash text-muted"></i>
                            </span>
                            <input type="search" id="papeleta" name="papeleta" class="form-control" value="{{ $papeletaBuscada }}" placeholder="Ej.: 21829" autocomplete="off" >
                        </div>
                    </div>
                    <div class="col-md-8 col-xl-4">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre del socio
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input  type="search" id="nombre" name="nombre" class="form-control" value="{{ $nombreBuscado }}" placeholder="Buscar por nombre o apellido" autocomplete="off" >
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                    @if(
                        $clasificacionSeleccionada !== ''
                        || $papeletaBuscada !== ''
                        || $nombreBuscado !== ''
                    )
                        <div class="col-auto">
                            <a href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index', $lote) }}"
                                class="btn btn-outline-secondary" >
                                <i class="bi bi-x-circle me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Origen</th>
                            <th>CODIGO_PERSONAL</th>
                            <th>Socio</th>
                            <th class="text-end">Monto asignado</th>
                            <th class="text-end">Cuota fija BD</th>
                            <th class="text-end">Diferencia</th>
                            <th>Préstamo</th>
                            <th>Clasificación</th>
                            <th class="text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conciliaciones as $conciliacion)
                            @php
                                $detallePrincipal = $conciliacion->detalles->first();
                            @endphp
                            <tr>
                                <td class="small">
                                    <div>{{ $conciliacion->registro?->archivo?->nombre_original }}</div>
                                    <div class="text-muted">
                                        Fila {{ $conciliacion->registro?->fila_origen }}
                                    </div>
                                </td>
                                <td class="fw-semibold">
                                    {{ $conciliacion->registro?->codigo_personal_normalizado ?: '—' }}
                                </td>
                                <td>
                                    <div>{{ $conciliacion->registro?->nombres }}</div>
                                    <div class="text-muted small">
                                        CI {{ $conciliacion->registro?->carnet ?: '—' }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $conciliacion->monto_excel_asignado, 2, ',', '.') }}
                                    <div class="text-muted small">
                                        Ajustado:
                                        {{ number_format((float) $conciliacion->monto_excel, 2, ',', '.') }}
                                    </div>
                                    @if($conciliacion->registro)
                                        <div class="text-muted small">
                                            Total Excel:
                                            {{ number_format((float) $conciliacion->registro->monto_descuento, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold
                                    {{ (float) $conciliacion->diferencia < -0.01
                                        ? 'text-danger'
                                        : ((float) $conciliacion->diferencia > 0.01
                                            ? 'text-warning'
                                            : 'text-success') }}"
                                >
                                    {{ number_format((float) $conciliacion->diferencia, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($detallePrincipal)
                                        <div class="fw-semibold">
                                            Tipo {{ $detallePrincipal->tipo_prestamo }}
                                        </div>
                                        <div class="text-muted small">
                                            Solicitud {{ $detallePrincipal->id_solicitud }}
                                            · Cuota N.º {{ $detallePrincipal->nro_cuota }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $conciliacion->clase_badge }}">
                                        {{ $conciliacion->clasificacion_texto }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalDetalleConciliacion"
                                        data-detalle-template="detalleConciliacion{{ $conciliacion->id }}"
                                        data-codigo-personal="{{ $conciliacion->registro?->codigo_personal_normalizado }}"
                                        title="Ver detalle"
                                        aria-label="Ver detalle de {{ $conciliacion->registro?->codigo_personal_normalizado }}"
                                    >
                                        <i class="bi bi-eye-fill me-1"></i>
                                        Detalle
                                    </button>

                                    <template id="detalleConciliacion{{ $conciliacion->id }}">
                                        <div class="alert alert-light border mb-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                                                <div>{{ $conciliacion->observacion }}</div>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Monto asignado del Excel</div>
                                                    <div class="fw-bold fs-5">
                                                        {{ number_format((float) $conciliacion->monto_excel_asignado, 2, ',', '.') }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        Ajustado para comparar:
                                                        {{ number_format((float) $conciliacion->monto_excel, 2, ',', '.') }}
                                                    </div>
                                                    @if($conciliacion->registro)
                                                        <div class="text-muted small">
                                                            Total original del Excel:
                                                            {{ number_format((float) $conciliacion->registro->monto_descuento, 2, ',', '.') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Cuota fija BD</div>
                                                    <div class="fw-bold fs-5">
                                                        {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Diferencia</div>
                                                    <div class="fw-bold fs-5
                                                        {{ (float) $conciliacion->diferencia < -0.01
                                                            ? 'text-danger'
                                                            : ((float) $conciliacion->diferencia > 0.01
                                                                ? 'text-warning'
                                                                : 'text-success') }}"
                                                    >
                                                        {{ number_format((float) $conciliacion->diferencia, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($conciliacion->detalles->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Solicitud</th>
                                                            <th>Cuota</th>
                                                            <th>Tipo de préstamo</th>
                                                            <th>Grupo</th>
                                                            <th class="text-end">Cuota fija</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($conciliacion->detalles as $detalle)
                                                            <tr>
                                                                <td>{{ $detalle->id_solicitud }}</td>
                                                                <td>
                                                                    N.º {{ $detalle->nro_cuota }}
                                                                    <div class="text-muted small">
                                                                        ID {{ $detalle->id_cuota_solicitud }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    {{ $detalle->tipo_prestamo }}
                                                                    <div class="text-muted small">
                                                                        {{ $detalle->descripcion_tipo ?: 'Sin descripción' }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @if($detalle->grupo_comparacion)
                                                                        <span class="badge bg-secondary">
                                                                            {{ str_replace('_', ' ', $detalle->grupo_comparacion) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-info text-dark">
                                                                            NO CLASIFICADO
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end fw-semibold">
                                                                    {{ number_format((float) $detalle->monto_cuota, 2, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr class="fw-bold">
                                                            <td colspan="4" class="text-end">
                                                                cuotas_solicitud.cuota_fija
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center text-muted border rounded-3 py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                No existen cuotas asociadas para mostrar.
                                            </div>
                                        @endif
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    No existen resultados para el filtro seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($conciliaciones->hasPages())
                <div class="card-footer bg-white">
                    {{ $conciliaciones->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="modal fade"
        id="modalDetalleConciliacion"
        tabindex="-1"
        aria-labelledby="modalDetalleConciliacionTitulo"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <div>
                        <h5 class="modal-title" id="modalDetalleConciliacionTitulo">
                            <i class="bi bi-file-earmark-text-fill me-2"></i>
                            Detalle de conciliación
                        </h5>
                        <div class="small opacity-75" id="modalDetalleConciliacionSubtitulo"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalDetalleConciliacionContenido">
                    <div class="text-center text-muted py-4">
                        Seleccione un registro para consultar su detalle.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="overlayComparandoPrestamos"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
    >
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div
                class="spinner-border text-success mb-3"
                style="width: 3rem; height: 3rem;"
                aria-hidden="true"
            ></div>
            <h5 class="mb-1">Comparando préstamos...</h5>
            <p class="text-muted mb-0">
                Espere mientras se consultan y clasifican todos los registros.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formulario = document.getElementById('formRecompararPrestamos');
                const boton = document.getElementById('btnRecompararPrestamos');
                const overlay = document.getElementById('overlayComparandoPrestamos');
                const modalDetalle = document.getElementById('modalDetalleConciliacion');
                const modalSubtitulo = document.getElementById('modalDetalleConciliacionSubtitulo');
                const modalContenido = document.getElementById('modalDetalleConciliacionContenido');

                if (formulario && boton && overlay) {
                    formulario.addEventListener('submit', function () {
                        boton.disabled = true;
                        overlay.classList.remove('d-none');
                        overlay.classList.add('d-flex');
                    });
                }

                if (modalDetalle && modalSubtitulo && modalContenido) {
                    modalDetalle.addEventListener('show.bs.modal', function (event) {
                        const botonDetalle = event.relatedTarget;
                        const templateId = botonDetalle?.dataset.detalleTemplate;
                        const template = templateId
                            ? document.getElementById(templateId)
                            : null;

                        modalSubtitulo.textContent = botonDetalle?.dataset.codigoPersonal
                            ? `CODIGO_PERSONAL: ${botonDetalle.dataset.codigoPersonal}`
                            : '';

                        if (template instanceof HTMLTemplateElement) {
                            modalContenido.replaceChildren(
                                template.content.cloneNode(true)
                            );
                            return;
                        }

                        modalContenido.innerHTML = `
                            <div class="alert alert-warning mb-0">
                                No fue posible cargar el detalle seleccionado.
                            </div>
                        `;
                    });

                    modalDetalle.addEventListener('hidden.bs.modal', function () {
                        modalSubtitulo.textContent = '';
                        modalContenido.replaceChildren();
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
