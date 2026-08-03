<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1"><i class="bi bi-folder2-open text-primary me-2"></i>Otros archivos de aportes</h5>
                <div class="text-muted small">
                    Planilla adicional MinDef; solo se importarán asociados con valor en la columna APORTE.
                </div>
            </div>
            @if($otrosArchivos->isNotEmpty() && $puedeModificar)
                <form method="POST" action="{{ route('procesamiento-mensual.lotes.certificados.otros.limpiar', $lote) }}"
                    onsubmit="return confirm('¿Eliminar todos los otros archivos de aportes?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                        <i class="bi bi-trash3-fill me-1"></i>Limpiar otros archivos
                    </button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($archivos->count() < 3)
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Primero debe cargar al menos 3 archivos principales de Certificados de Aportes.
            </div>
        @elseif($puedeModificar)
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                data-bs-target="#modalOtroArchivoAporte">
                <i class="bi bi-folder-plus me-1"></i>Otros archivos
            </button>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-lock-fill me-2"></i>El lote no admite nuevas cargas.
            </div>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Archivo adicional</th>
                    <th class="text-center">Asociados con aporte</th>
                    <th class="text-end">Total aporte</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha de carga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($otrosArchivos as $archivo)
                    <tr>
                        <td><i class="bi bi-file-earmark-excel text-success me-1"></i>{{ $archivo->nombre_original }}</td>
                        <td class="text-center">{{ $archivo->filas_importadas }}</td>
                        <td class="text-end">Bs {{ number_format((float) $archivo->total_monto_descuento, 2, ',', '.') }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $archivo->estado }}</span></td>
                        <td>{{ $archivo->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No existen otros archivos de aportes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($archivos->count() >= 3 && $puedeModificar)
    <div class="modal fade" id="modalOtroArchivoAporte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form id="formOtroArchivoAporte" method="POST"
                    action="{{ route('procesamiento-mensual.lotes.certificados.otros.store', $lote) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="hash_preview" id="hashPreviewAporte">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Otros archivos de aportes
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            El nombre debe ser <strong>planilla_mindef_MM_AAAA.xlsx</strong> y corresponder a
                            <strong>{{ $lote->periodo }}</strong>. Se usarán PERSON, CARNET, GRADO, NOMBRES,
                            DESTINO y <strong>APORTE</strong>; las demás columnas serán ignoradas.
                        </div>
                        <div id="erroresOtroAporte" class="alert alert-danger d-none">
                            <ul id="listaErroresOtroAporte" class="mb-0 ps-3"></ul>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-9">
                                <label for="archivoOtroAporte" class="form-label fw-semibold">Planilla adicional MinDef</label>
                                <input id="archivoOtroAporte" name="archivo" type="file" class="form-control"
                                    accept=".xlsx,.xls">
                            </div>
                            <div class="col-lg-3">
                                <button id="btnPreviewOtroAporte" type="button" class="btn btn-primary w-100">
                                    <i class="bi bi-eye-fill me-1"></i>Previsualizar
                                </button>
                            </div>
                        </div>
                        <div id="previewOtroAporte" class="d-none mt-4">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4"><div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted">Filas a incorporar</div>
                                    <div class="fs-4 fw-bold" id="filasOtroAporte">0</div>
                                </div></div>
                                <div class="col-md-4"><div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted">Total aporte</div>
                                    <div class="fs-4 fw-bold" id="totalOtroAporte">Bs 0,00</div>
                                </div></div>
                                <div class="col-md-4"><div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted">Filas omitidas sin aporte</div>
                                    <div class="fs-4 fw-bold" id="omitidasOtroAporte">0</div>
                                </div></div>
                            </div>
                            <div id="duplicadasOtroAporte" class="alert alert-warning d-none"></div>
                            <div class="table-responsive border rounded-3">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light"><tr>
                                        <th>Fila</th><th>Papeleta</th><th>CI</th><th>Grado</th>
                                        <th>Nombres</th><th>Destino</th><th class="text-end">Aporte</th>
                                    </tr></thead>
                                    <tbody id="tablaOtroAporte"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white position-sticky bottom-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="btnGuardarOtroAporte" type="submit" class="btn btn-success" disabled>
                            <i class="bi bi-check-circle-fill me-1"></i>Confirmar e incorporar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formulario = document.getElementById('formOtroArchivoAporte');
                const archivo = document.getElementById('archivoOtroAporte');
                const botonPreview = document.getElementById('btnPreviewOtroAporte');
                const botonGuardar = document.getElementById('btnGuardarOtroAporte');
                const errores = document.getElementById('erroresOtroAporte');
                const listaErrores = document.getElementById('listaErroresOtroAporte');
                const preview = document.getElementById('previewOtroAporte');
                const moneda = new Intl.NumberFormat('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                const mostrarErrores = function (mensajes) {
                    listaErrores.innerHTML = '';
                    mensajes.forEach(function (mensaje) {
                        const item = document.createElement('li');
                        item.textContent = mensaje;
                        listaErrores.appendChild(item);
                    });
                    errores.classList.remove('d-none');
                };

                archivo.addEventListener('change', function () {
                    document.getElementById('hashPreviewAporte').value = '';
                    botonGuardar.disabled = true;
                    preview.classList.add('d-none');
                    errores.classList.add('d-none');
                });

                botonPreview.addEventListener('click', async function () {
                    if (!archivo.files.length) {
                        mostrarErrores(['Seleccione una planilla Excel.']);
                        return;
                    }

                    botonPreview.disabled = true;
                    errores.classList.add('d-none');
                    const datosFormulario = new FormData();
                    datosFormulario.append('archivo', archivo.files[0]);
                    datosFormulario.append('_token', formulario.querySelector('[name="_token"]').value);

                    try {
                        const respuesta = await fetch(@json(route('procesamiento-mensual.lotes.certificados.otros.preview', $lote)), {
                            method: 'POST', body: datosFormulario, headers: {'Accept': 'application/json'}
                        });
                        const datos = await respuesta.json();

                        if (!respuesta.ok) {
                            mostrarErrores(datos.errors
                                ? Object.values(datos.errors).flat()
                                : [datos.message || 'No se pudo previsualizar el archivo.']);
                            return;
                        }

                        document.getElementById('hashPreviewAporte').value = datos.hash;
                        document.getElementById('filasOtroAporte').textContent = datos.filas;
                        document.getElementById('totalOtroAporte').textContent = 'Bs ' + moneda.format(datos.total_aporte);
                        document.getElementById('omitidasOtroAporte').textContent = datos.omitidas_sin_aporte;
                        const duplicadas = document.getElementById('duplicadasOtroAporte');
                        duplicadas.classList.toggle('d-none', datos.duplicadas.length === 0);
                        duplicadas.textContent = datos.duplicadas.length
                            ? datos.duplicadas.length + ' papeleta(s) ya existentes fueron omitidas.' : '';
                        const cuerpo = document.getElementById('tablaOtroAporte');
                        cuerpo.innerHTML = '';
                        datos.registros.forEach(function (fila) {
                            const tr = document.createElement('tr');
                            [fila.fila, fila.papeleta, fila.carnet, fila.grado, fila.nombres, fila.destino].forEach(function (valor) {
                                const td = document.createElement('td'); td.textContent = valor ?? ''; tr.appendChild(td);
                            });
                            const monto = document.createElement('td');
                            monto.className = 'text-end'; monto.textContent = moneda.format(fila.aporte); tr.appendChild(monto);
                            cuerpo.appendChild(tr);
                        });
                        preview.classList.remove('d-none');
                        botonGuardar.disabled = false;
                    } catch (error) {
                        mostrarErrores(['No fue posible comunicarse con el servidor.']);
                    } finally {
                        botonPreview.disabled = false;
                    }
                });

                formulario.addEventListener('submit', function () {
                    botonGuardar.disabled = true;
                    botonGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Incorporando...';
                });
            });
        </script>
    @endpush
@endif
