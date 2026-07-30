<x-app-layout>
    <x-slot name="header">
        Gestionar archivos · {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    No fue posible importar los archivos
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
                <h4 class="mb-1">Archivos del lote {{ $lote->periodo }}</h4>
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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-bank text-success me-2"></i>
                    Préstamos
                </h5>
            </div>
            <div class="card-body">
                @if($puedeCargar)
                    <form
                        id="formCargaPrestamos"
                        method="POST"
                        action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.store', $lote) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <div class="row g-3 align-items-end">
                            <div class="col-xl-9">
                                <label for="archivos" class="form-label fw-semibold">
                                    Archivos Excel de préstamos
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('archivos') is-invalid @enderror"
                                    id="archivos"
                                    name="archivos[]"
                                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                    multiple
                                    required
                                >
                                <div class="form-text">
                                    Seleccione entre 1 y 15 archivos .xlsx o .xls.
                                    Cada archivo debe corresponder a
                                    <strong>{{ $lote->nombre_mes }} {{ $lote->gestion }}</strong>.
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <button
                                    type="submit"
                                    class="btn btn-success w-100"
                                    id="btnCargarPrestamos"
                                >
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i>
                                    Cargar y consolidar
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-lock-fill me-2"></i>
                        La carga está bloqueada porque el lote se encuentra
                        <strong>{{ $lote->estado }}</strong>.
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Archivos cargados</div>
                        <div class="fs-4 fw-bold">{{ $archivos->count() }}</div>
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
                        <div class="text-muted small">Total neto</div>
                        <div class="fs-5 fw-bold">
                            Bs {{ number_format((float) $resumen->tot_2, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Comisión</div>
                        <div class="fs-5 fw-bold">
                            Bs {{ number_format((float) $resumen->comision, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-files text-primary me-2"></i>
                    Archivos incorporados
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Archivo</th>
                            <th class="text-center">Filas</th>
                            <th class="text-end">Monto descuento</th>
                            <th class="text-end">Total neto</th>
                            <th class="text-end">Comisión</th>
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
                                    {{ number_format((float) $archivo->total_tot_2, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $archivo->total_comision, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $archivo->estado }}</span>
                                </td>
                                <td>{{ $archivo->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aún no se cargaron archivos de préstamos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-table text-success me-2"></i>
                        Tabla consolidada de préstamos
                    </h5>
                    <span class="text-muted small">
                        {{ number_format((int) $resumen->filas) }} registros
                    </span>
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
                            <th class="text-end">TOT_2</th>
                            <th class="text-end">COMISION</th>
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
                                    {{ number_format((float) $registro->tot_2, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $registro->comision, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="text-center text-muted py-4">
                                    La tabla consolidada está vacía.
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

        @if($archivos->isNotEmpty())
            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                @if($puedeCargar)
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#modalLimpiarImportacion"
                    >
                        <i class="bi bi-trash3-fill me-1"></i>
                        Limpiar importación
                    </button>
                @endif

                @if($puedeCargar)
                    <form
                        id="formCompararPrestamos"
                        method="POST"
                        action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.comparar', $lote) }}"
                    >
                        @csrf
                        <button
                            type="submit"
                            id="btnCompararPrestamos"
                            class="btn btn-success"
                        >
                            Continuar
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>
                @else
                    <button
                        type="button"
                        class="btn btn-secondary"
                        disabled
                        title="El lote procesado no puede volver a compararse"
                    >
                        <i class="bi bi-lock-fill me-1"></i>
                        Comparación bloqueada
                    </button>

                    <a
                        href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index', $lote) }}"
                        class="btn btn-outline-primary"
                    >
                        <i class="bi bi-eye-fill me-1"></i>
                        Consultar comparación
                    </a>
                @endif
            </div>
        @endif
    </div>

    @if($archivos->isNotEmpty() && $puedeCargar)
        <div
            class="modal fade"
            id="modalLimpiarImportacion"
            tabindex="-1"
            aria-labelledby="modalLimpiarImportacionLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalLimpiarImportacionLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Limpiar importación de préstamos
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            Se eliminarán los archivos de préstamos cargados y todos
                            sus registros de la tabla consolidada.
                        </p>

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            Esta acción no se puede deshacer. Después podrá cargar
                            un nuevo grupo de archivos Excel.
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
                            action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.limpiar', $lote) }}"
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

    <div
        id="overlayConsolidando"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
        aria-label="Consolidando archivos"
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

    <div
        id="overlayComparando"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
        aria-label="Comparando préstamos"
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
                const formularioCarga = document.getElementById('formCargaPrestamos');
                const botonCarga = document.getElementById('btnCargarPrestamos');
                const overlayCarga = document.getElementById('overlayConsolidando');
                const formularioComparacion = document.getElementById('formCompararPrestamos');
                const botonComparacion = document.getElementById('btnCompararPrestamos');
                const overlayComparacion = document.getElementById('overlayComparando');

                if (formularioCarga && botonCarga && overlayCarga) {
                    formularioCarga.addEventListener('submit', function () {
                        botonCarga.disabled = true;
                        overlayCarga.classList.remove('d-none');
                        overlayCarga.classList.add('d-flex');
                    });
                }

                if (formularioComparacion && botonComparacion && overlayComparacion) {
                    formularioComparacion.addEventListener('submit', function () {
                        botonComparacion.disabled = true;
                        overlayComparacion.classList.remove('d-none');
                        overlayComparacion.classList.add('d-flex');
                    });
                }

                window.addEventListener('pageshow', function () {
                    if (botonCarga && overlayCarga) {
                        botonCarga.disabled = false;
                        overlayCarga.classList.add('d-none');
                        overlayCarga.classList.remove('d-flex');
                    }

                    if (botonComparacion && overlayComparacion) {
                        botonComparacion.disabled = false;
                        overlayComparacion.classList.add('d-none');
                        overlayComparacion.classList.remove('d-flex');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
