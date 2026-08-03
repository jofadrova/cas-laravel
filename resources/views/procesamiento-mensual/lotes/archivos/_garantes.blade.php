<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-people-fill text-success me-2"></i>
                    Descuentos pagados por garantes
                </h5>
                <div class="text-muted small">
                    Archivo complementario de Cartera de Crédito para préstamos REGULARES.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($registrosGarantes->isNotEmpty())
                    <button type="button" class="btn btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#modalSeguimientoGarantes">
                        <i class="bi bi-hourglass-split me-1"></i>
                        Seguimiento de garantes
                        <span class="badge bg-primary ms-1">{{ number_format($registrosGarantes->count()) }}</span>
                    </button>
                @endif
                @if($archivosGarantes->isNotEmpty() && $puedeCargarGarantes)
                    <button type="button" class="btn btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#modalLimpiarGarantes">
                        <i class="bi bi-trash3-fill me-1"></i> Limpiar garantes
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($puedeCargarGarantes)
            <form id="formCargaGarantes" method="POST"
                action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.garantes.store', $lote) }}"
                enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-xl-9">
                        <label for="archivos_garantes" class="form-label fw-semibold">
                            Archivo Excel de descuentos a garantes
                        </label>
                        <input type="file" id="archivos_garantes" name="archivos_garantes[]"
                            class="form-control @error('archivos_garantes') is-invalid @enderror"
                            accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                            multiple required>
                        <div class="form-text">
                            Periodo <strong>{{ $lote->periodo }}</strong>. Puede seleccionar hasta 5 archivos.
                            Una nueva carga reemplaza la importación anterior y actualiza el seguimiento.
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <button type="submit" id="btnCargarGarantes" class="btn btn-success w-100">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Subir archivos de garantes
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-lock-fill me-2"></i>
                La carga de garantes está bloqueada porque el lote se encuentra
                <strong>{{ $lote->estado }}</strong>.
            </div>
        @endif
    </div>
</div>

@if($registrosGarantes->isNotEmpty())
    <div class="modal fade" id="modalSeguimientoGarantes" tabindex="-1"
        aria-labelledby="modalSeguimientoGarantesTitulo" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title" id="modalSeguimientoGarantesTitulo">
                            <i class="bi bi-hourglass-split me-2"></i> Seguimiento de descuentos a garantes
                        </h5>
                        <div class="small opacity-75">Detalle del garante que paga y del titular beneficiado.</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Origen</th><th>Garante que paga</th><th>Titular beneficiado</th>
                                    <th class="text-end">Informado Bs</th><th class="text-end">Verificado ÷ 6,96</th>
                                    <th class="text-end">Acumulado / saldo</th><th>Solicitud / cuota</th>
                                    <th>Conciliación</th><th>Aplicación</th><th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrosGarantes as $registroGarante)
                                    <tr>
                                        <td class="small">
                                            <div>{{ $registroGarante->archivo?->nombre_original }}</div>
                                            <div class="text-muted">Fila {{ $registroGarante->fila_origen }}</div>
                                        </td>
                                        <td><div class="fw-semibold">{{ $registroGarante->nombre_garante }}</div>
                                            <div class="text-muted small">Papeleta {{ $registroGarante->codigo_garante }} · {{ $registroGarante->tipo_garante }}</div></td>
                                        <td><div>{{ $registroGarante->nombre_titular }}</div>
                                            <div class="text-muted small">Papeleta {{ $registroGarante->codigo_titular }}</div></td>
                                        <td class="text-end fw-semibold">{{ number_format((float) $registroGarante->monto_bs, 2, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format((float) $registroGarante->monto_aplicable, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <div>{{ number_format((float) $registroGarante->monto_acumulado, 2, ',', '.') }}</div>
                                            <div class="text-muted small">Falta {{ number_format((float) $registroGarante->saldo_pendiente, 2, ',', '.') }}</div>
                                        </td>
                                        <td>
                                            @if($registroGarante->id_solicitud)
                                                <div>Solicitud {{ $registroGarante->id_solicitud }}</div>
                                                <div class="text-muted small">Cuota ID {{ $registroGarante->id_cuota_solicitud }}</div>
                                            @else
                                                <span class="text-muted">Sin identificar</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $registroGarante->estado_conciliacion === 'COINCIDE' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ str_replace('_', ' ', $registroGarante->estado_conciliacion) }}</span></td>
                                        <td><span class="badge {{ $registroGarante->clase_aplicacion }}">
                                            {{ str_replace('_', ' ', $registroGarante->estado_aplicacion) }}</span>
                                            @if($registroGarante->estado_aplicacion === \App\Models\LoteGaranteRegistro::APLICACION_PENDIENTE)
                                                <div class="small text-warning-emphasis mt-1">
                                                    <i class="bi bi-calendar-plus me-1"></i>Completar el próximo mes
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small" style="min-width: 240px;">{{ $registroGarante->observacion_sistema ?: $registroGarante->observacion_excel }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <span class="text-muted small me-auto">{{ number_format($registrosGarantes->count()) }} registros</span>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if($archivosGarantes->isNotEmpty() && $puedeCargarGarantes)
    <div class="modal fade" id="modalLimpiarGarantes" tabindex="-1"
        aria-labelledby="modalLimpiarGarantesTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalLimpiarGarantesTitulo">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Limpiar descuentos a garantes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Se eliminarán los archivos de garantes, sus registros y sus comparaciones. Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.garantes.limpiar', $lote) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash3-fill me-1"></i> Sí, limpiar garantes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="overlayCargandoGarantes"
    class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
    style="z-index: 2000; background: rgba(15, 23, 42, .68);" role="status" aria-live="polite">
    <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
        <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" aria-hidden="true"></div>
        <h5 class="mb-1">Cargando y comparando garantes...</h5>
        <p class="text-muted mb-0">Espere mientras se importa el Excel y se actualiza el seguimiento.</p>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formulario = document.getElementById('formCargaGarantes');
            const boton = document.getElementById('btnCargarGarantes');
            const overlay = document.getElementById('overlayCargandoGarantes');
            if (formulario && boton && overlay) {
                formulario.addEventListener('submit', function () {
                    boton.disabled = true;
                    overlay.classList.remove('d-none');
                    overlay.classList.add('d-flex');
                });
                window.addEventListener('pageshow', function () {
                    boton.disabled = false;
                    overlay.classList.add('d-none');
                    overlay.classList.remove('d-flex');
                });
            }
        });
    </script>
@endpush
