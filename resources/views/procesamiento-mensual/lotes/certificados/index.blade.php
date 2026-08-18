<x-app-layout>
    <x-slot name="header">
        Gestionar Certificados de Aportes · {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        @foreach(['success' => 'success', 'info' => 'info', 'error' => 'danger'] as $sesion => $clase)
            @if(session($sesion))
                <div class="alert alert-{{ $clase }} alert-dismissible fade show" role="alert">
                    <i class="bi bi-{{ $clase === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' }} me-2"></i>
                    {{ session($sesion) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    No fue posible importar los archivos de Certificados de Aportes
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Certificados de Aportes del lote {{ $lote->periodo }}</h4>
                <div class="text-muted">
                    Código {{ $lote->codigo_periodo }}
                    <span class="mx-1">·</span>
                    <span class="badge rounded-pill {{ $lote->clase_estado }}">
                        {{ $lote->estado }}
                    </span>
                </div>
            </div>
            <a
                href="{{ route('procesamiento-mensual.lotes.show', $lote) }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Volver al lote
            </a>
        </div>

        @if((int) $resumen->filas > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-diagram-3-fill text-success me-2"></i>
                            Separación de aportes
                        </h5>
                        <div class="text-muted small">
                            Distribuye cada monto entre aporte obligatorio (AO), voluntario (AV) e individual (AI).
                            @if($registrosSeparados > 0)
                                {{ number_format($registrosSeparados) }} registro(s) ya fueron separados.
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($registrosSeparados > 0)
                            <a href="{{ route('procesamiento-mensual.lotes.certificados.separacion.index', $lote) }}" class="btn btn-outline-primary">
                                <i class="bi bi-clipboard-data me-1"></i>Ver separación
                            </a>
                        @endif
                        <button
                            type="button"
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#modalSepararAportes"
                            @disabled(! $puedeModificar)
                        >
                            <i class="bi bi-diagram-3 me-1"></i>Separar aportes
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-award text-success me-2"></i>
                    Carga de archivos de Certificados de Aportes
                </h5>
            </div>
            <div class="card-body">
                @if($puedeCargar)
                    <form
                        id="formCargaCertificados"
                        method="POST"
                        action="{{ route('procesamiento-mensual.lotes.certificados.store', $lote) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-9">
                                <label for="archivosCertificados" class="form-label fw-semibold">
                                    Archivos Excel de Certificados de Aportes
                                </label>
                                <input
                                    type="file"
                                    class="form-control"
                                    id="archivosCertificados"
                                    name="archivos[]"
                                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                    multiple
                                    required
                                >
                                <div class="form-text">
                                    El lote debe reunir entre <strong>3 y 10 archivos</strong>.
                                    Actualmente existen {{ $archivos->count() }}; puede agregar hasta
                                    {{ $cantidadDisponible }}. Todos deben corresponder a
                                    <strong>{{ $lote->nombre_mes }} {{ $lote->gestion }}</strong>.
                                </div>
                                <div id="mensajeSeleccionCertificados" class="small mt-2" aria-live="polite"></div>
                            </div>
                            <div class="col-xl-3">
                                <button
                                    type="submit"
                                    class="btn btn-success w-100"
                                    id="btnCargarCertificados"
                                >
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i>
                                    Cargar y consolidar
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif($puedeModificar)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        El lote ya alcanzó el máximo de 10 archivos de Certificados de Aportes.
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-lock-fill me-2"></i>
                        La carga está bloqueada porque el lote se encuentra
                        <strong>{{ $lote->estado }}</strong>. La información permanece disponible
                        para consulta.
                    </div>
                @endif
            </div>
        </div>

        @include('procesamiento-mensual.lotes.certificados._otros-archivos')

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Archivos cargados</div>
                        <div class="fs-4 fw-bold">{{ $archivos->count() }}</div>
                        <div class="small {{ $archivos->count() >= 3 ? 'text-success' : 'text-warning' }}">
                            {{ $archivos->count() >= 3 ? 'Cantidad válida' : 'Mínimo requerido: 3' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Filas consolidadas</div>
                        <div class="fs-4 fw-bold">{{ number_format((int) $resumen->filas) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Monto descuento</div>
                        <div class="fs-5 fw-bold">
                            Bs {{ number_format((float) $resumen->monto_descuento, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Tasa regulación</div>
                        <div class="fs-5 fw-bold">
                            Bs {{ number_format((float) $resumen->tasa_regulacion, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total descuento</div>
                        <div class="fs-5 fw-bold">
                            Bs {{ number_format((float) $resumen->total_descuento, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-files text-primary me-2"></i>
                        Archivos de Certificados de Aportes incorporados
                    </h5>
                    @if($archivos->isNotEmpty() && $puedeModificar)
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalLimpiarCertificados"
                        >
                            <i class="bi bi-trash3-fill me-1"></i>
                            Limpiar importación
                        </button>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Archivo</th>
                            <th class="text-center">Filas</th>
                            <th class="text-end">Monto descuento</th>
                            <th class="text-end">Tasa regulación</th>
                            <th class="text-end">Total descuento</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha de carga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivos as $archivo)
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                    {{ $archivo->nombre_original }}
                                </td>
                                <td class="text-center">{{ $archivo->filas_importadas }}</td>
                                <td class="text-end">
                                    {{ number_format((float) $archivo->total_monto_descuento, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $archivo->total_tasa_regulacion, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $archivo->total_descuento_calculado, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $archivo->estado }}</span>
                                </td>
                                <td>{{ $archivo->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aún no se cargaron archivos de Certificados de Aportes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-table text-success me-2"></i>
                            Tabla consolidada de Certificados de Aportes
                        </h5>
                        <span class="text-muted small">
                            {{ number_format((int) $resumen->filas) }} registros en total
                        </span>
                    </div>
                    <form method="GET" class="d-flex gap-2" role="search">
                        <input
                            type="search"
                            name="buscar"
                            value="{{ $buscar }}"
                            class="form-control form-control-sm"
                            placeholder="Papeleta, carnet o nombre"
                        >
                        <button class="btn btn-outline-primary btn-sm" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                        @if($buscar !== '')
                            <a
                                href="{{ route('procesamiento-mensual.lotes.certificados.index', $lote) }}"
                                class="btn btn-outline-secondary btn-sm"
                            >
                                Limpiar
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Archivo</th>
                            <th>Fila</th>
                            <th>GESTION</th>
                            <th>MES</th>
                            <th>DOCUMENTO_RESPALDO</th>
                            <th>EIT_CODORG</th>
                            <th>ORGANISMOS</th>
                            <th>EIT_CODREP</th>
                            <th>REPARTICION</th>
                            <th>GRUPO</th>
                            <th>DESCRION_GRUPO</th>
                            <th>IDENTIFICADOR_ACREEDOR</th>
                            <th>ACREEDOR</th>
                            <th>CODIGO_CONCEPTO</th>
                            <th>CODIGO_ACREEDOR</th>
                            <th>CTA_BANCARIA_ACREEDOR</th>
                            <th>CODIGO_PERSONAL</th>
                            <th>EIT_ITEM</th>
                            <th>CARNET</th>
                            <th>GRADO</th>
                            <th>MENSION</th>
                            <th>NOMBRES</th>
                            <th class="text-end">MONTO_DESCUENTO</th>
                            <th class="text-end">TASA_REGULACION</th>
                            <th class="text-end">TOTAL DESCUENTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td>{{ $registro->archivo?->nombre_original }}</td>
                                <td>{{ $registro->fila_origen }}</td>
                                <td>{{ $registro->gestion }}</td>
                                <td>{{ $registro->mes }}</td>
                                <td>{{ $registro->documento_respaldo }}</td>
                                <td>{{ $registro->eit_codorg }}</td>
                                <td>{{ $registro->organismos }}</td>
                                <td>{{ $registro->eit_codrep }}</td>
                                <td>{{ $registro->reparticion }}</td>
                                <td>{{ $registro->grupo }}</td>
                                <td>{{ $registro->descripcion_grupo }}</td>
                                <td>{{ $registro->identificador_acreedor }}</td>
                                <td>{{ $registro->acreedor }}</td>
                                <td>{{ $registro->codigo_concepto }}</td>
                                <td>{{ $registro->codigo_acreedor }}</td>
                                <td>{{ $registro->cta_bancaria_acreedor }}</td>
                                <td>{{ $registro->codigo_personal_normalizado }}</td>
                                <td>{{ $registro->eit_item }}</td>
                                <td>{{ $registro->carnet }}</td>
                                <td>{{ $registro->grado }}</td>
                                <td>{{ $registro->mension }}</td>
                                <td>{{ $registro->nombres }}</td>
                                <td class="text-end">
                                    {{ number_format((float) $registro->monto_descuento, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $registro->tasa_regulacion, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ number_format((float) $registro->total_descuento, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="text-center text-muted py-4">
                                    {{ $buscar !== ''
                                        ? 'No se encontraron registros con el criterio indicado.'
                                        : 'La tabla consolidada de Certificados de Aportes está vacía.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registros->hasPages())
                <div class="card-footer bg-white">
                    {{ $registros->links() }}
                </div>
            @endif
        </div>
    </div>

    @if($archivos->isNotEmpty() && $puedeModificar)
        <div
            class="modal fade"
            id="modalLimpiarCertificados"
            tabindex="-1"
            aria-labelledby="modalLimpiarCertificadosLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalLimpiarCertificadosLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Limpiar importación de Certificados de Aportes
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            Se eliminarán los {{ $archivos->count() }} archivos de Certificados de Aportes y todos
                            sus registros de la tabla consolidada.
                        </p>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            Esta acción no se puede deshacer. Después podrá cargar un
                            nuevo grupo de 3 a 10 archivos.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>
                        <form
                            method="POST"
                            action="{{ route('procesamiento-mensual.lotes.certificados.limpiar', $lote) }}"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash3-fill me-1"></i>
                                Sí, limpiar importación
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if((int) $resumen->filas > 0 && $puedeModificar)
        <div class="modal fade" id="modalSepararAportes" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar separación de aportes</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea separar los {{ number_format((int) $resumen->filas) }} registros consolidados?</p>
                        <div class="alert alert-info mb-0">
                            La separación usará TOTAL_DESCUENTO. El primer bloque completo de Bs 100 será AO,
                            los siguientes bloques de Bs 100 serán AV y el residuo será AI.
                            La suma AO + AV + AI conservará el TOTAL_DESCUENTO.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form id="formSepararAportes" method="POST" action="{{ route('procesamiento-mensual.lotes.certificados.separacion.separar', $lote) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger"><i class="bi bi-check-circle me-1"></i>Confirmar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="overlaySeparandoAportes" class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="z-index:2050;background:rgba(15,23,42,.68);">
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
            <h5>Separando aportes...</h5>
            <p class="text-muted mb-0">Calculando AO, AV y AI para cada asociado.</p>
        </div>
    </div>

    <div
        id="overlayConsolidandoCertificados"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
        aria-label="Consolidando archivos de Certificados de Aportes"
    >
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div
                class="spinner-border text-success mb-3"
                style="width: 3rem; height: 3rem;"
                aria-hidden="true"
            ></div>
            <h5 class="mb-1">Consolidando archivos...</h5>
            <p class="text-muted mb-0">
                Espere mientras se procesan los archivos Excel.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formularioSeparacion = document.getElementById('formSepararAportes');
                formularioSeparacion?.addEventListener('submit', function () {
                    const overlaySeparacion = document.getElementById('overlaySeparandoAportes');
                    overlaySeparacion.classList.remove('d-none');
                    overlaySeparacion.classList.add('d-flex');
                    formularioSeparacion.querySelector('button[type="submit"]').disabled = true;
                });

                const formulario = document.getElementById('formCargaCertificados');
                const entrada = document.getElementById('archivosCertificados');
                const boton = document.getElementById('btnCargarCertificados');
                const mensaje = document.getElementById('mensajeSeleccionCertificados');
                const overlay = document.getElementById('overlayConsolidandoCertificados');
                const existentes = @json($archivos->count());
                const minimoPendiente = @json($cantidadMinimaPendiente);
                const disponibles = @json($cantidadDisponible);

                if (!formulario || !entrada || !boton || !mensaje || !overlay) {
                    return;
                }

                entrada.addEventListener('change', function () {
                    const seleccionados = entrada.files.length;
                    const total = existentes + seleccionados;
                    const valido = seleccionados >= minimoPendiente
                        && seleccionados <= disponibles
                        && total >= 3
                        && total <= 10;

                    mensaje.className = 'small mt-2 ' + (valido ? 'text-success' : 'text-danger');
                    mensaje.textContent = seleccionados === 0
                        ? ''
                        : seleccionados + ' archivo(s) seleccionado(s); el lote tendrá '
                            + total + ' archivo(s).';
                    boton.disabled = !valido;
                });

                formulario.addEventListener('submit', function (evento) {
                    if (formulario.dataset.enviando === '1') {
                        evento.preventDefault();
                        return;
                    }

                    formulario.dataset.enviando = '1';
                    boton.disabled = true;
                    overlay.classList.remove('d-none');
                    overlay.classList.add('d-flex');
                });

                window.addEventListener('pageshow', function () {
                    formulario.dataset.enviando = '0';
                    overlay.classList.add('d-none');
                    overlay.classList.remove('d-flex');
                    entrada.dispatchEvent(new Event('change'));
                });
            });
        </script>
    @endpush
</x-app-layout>
