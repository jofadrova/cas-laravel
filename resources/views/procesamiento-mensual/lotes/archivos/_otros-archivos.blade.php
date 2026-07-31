{{-- Modal exclusivo para incorporar planilla_mindef_MM_AAAA.xlsx en Préstamos. --}}
@if(! $procesamientoPago)
    <div
        class="modal fade"
        id="modalOtrosArchivosPrestamos"
        tabindex="-1"
        aria-labelledby="modalOtrosArchivosPrestamosLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form
                    id="formOtroArchivoPrestamos"
                    method="POST"
                    action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.otros.store', $lote) }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <input type="hidden" name="hash_preview" id="hashPreviewOtroArchivo">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalOtrosArchivosPrestamosLabel">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>
                            Otros archivos de Préstamos
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Seleccione la planilla adicional
                            <strong>planilla_mindef_MM_AAAA.xlsx</strong>.
                            Para Préstamos solo se usarán las columnas
                            <strong>PRESTAMO</strong> y <strong>SER ADM</strong>.
                            Las columnas APORTE y FVS no se incorporarán en este proceso.
                        </div>

                        <div id="erroresOtroArchivo" class="alert alert-danger d-none" role="alert">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                No fue posible previsualizar la planilla
                            </div>
                            <ul class="mb-0 ps-3" id="listaErroresOtroArchivo"></ul>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-lg-9">
                                <label for="archivoOtroPrestamo" class="form-label fw-semibold">
                                    Planilla adicional de MinDef
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('archivo') is-invalid @enderror"
                                    id="archivoOtroPrestamo"
                                    name="archivo"
                                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                    required
                                >
                                <div class="form-text">
                                    Debe corresponder al período
                                    <strong>{{ $lote->codigo_periodo }}</strong>.
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <button
                                    type="button"
                                    class="btn btn-primary w-100"
                                    id="btnPrevisualizarOtroArchivo"
                                >
                                    <i class="bi bi-eye-fill me-1"></i>
                                    Previsualizar
                                </button>
                            </div>
                        </div>

                        <div id="vistaPreviaOtroArchivo" class="d-none mt-4">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6 col-xl-3">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted">Filas a incorporar</div>
                                        <div class="fs-4 fw-bold" id="previewFilasOtroArchivo">0</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted">Préstamo</div>
                                        <div class="fs-5 fw-bold" id="previewPrestamoOtroArchivo">Bs 0,00</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted">Servicio administrativo</div>
                                        <div class="fs-5 fw-bold" id="previewComisionOtroArchivo">Bs 0,00</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted">Total neto</div>
                                        <div class="fs-5 fw-bold" id="previewNetoOtroArchivo">Bs 0,00</div>
                                    </div>
                                </div>
                            </div>

                            <div id="observacionesOtroArchivo" class="alert alert-warning d-none"></div>

                            <div class="table-responsive border rounded-3">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fila</th>
                                            <th>Papeleta</th>
                                            <th>CI</th>
                                            <th>Grado</th>
                                            <th>Nombres</th>
                                            <th>Destino</th>
                                            <th class="text-end">Préstamo</th>
                                            <th class="text-end">Ser. Adm.</th>
                                            <th class="text-end">Neto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaPreviewOtroArchivo"></tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Revise los datos antes de confirmar. Al incorporarlos se
                                recalculará toda la comparación de Préstamos y se buscará
                                la deuda correspondiente de cada papeleta en la base de datos.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            id="btnConfirmarOtroArchivo"
                            disabled
                        >
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Confirmar e incorporar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElemento = document.getElementById('modalOtrosArchivosPrestamos');
                const formulario = document.getElementById('formOtroArchivoPrestamos');
                const archivo = document.getElementById('archivoOtroPrestamo');
                const botonPreview = document.getElementById('btnPrevisualizarOtroArchivo');
                const botonConfirmar = document.getElementById('btnConfirmarOtroArchivo');
                const hashPreview = document.getElementById('hashPreviewOtroArchivo');
                const vistaPrevia = document.getElementById('vistaPreviaOtroArchivo');
                const errores = document.getElementById('erroresOtroArchivo');
                const listaErrores = document.getElementById('listaErroresOtroArchivo');
                const tabla = document.getElementById('tablaPreviewOtroArchivo');
                const observaciones = document.getElementById('observacionesOtroArchivo');
                const urlPreview = @json(route('procesamiento-mensual.lotes.archivos.prestamos.otros.preview', $lote));
                const csrf = formulario.querySelector('input[name="_token"]').value;
                const moneda = new Intl.NumberFormat('es-BO', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                function reiniciarPreview() {
                    hashPreview.value = '';
                    botonConfirmar.disabled = true;
                    vistaPrevia.classList.add('d-none');
                    errores.classList.add('d-none');
                    listaErrores.replaceChildren();
                    tabla.replaceChildren();
                    observaciones.classList.add('d-none');
                    observaciones.textContent = '';
                }

                function mostrarErrores(mensajes) {
                    listaErrores.replaceChildren();

                    mensajes.forEach(function (mensaje) {
                        const item = document.createElement('li');
                        item.textContent = mensaje;
                        listaErrores.appendChild(item);
                    });

                    errores.classList.remove('d-none');
                }

                function agregarCelda(fila, valor, clase = '') {
                    const celda = document.createElement('td');
                    celda.textContent = valor ?? '';
                    if (clase) celda.className = clase;
                    fila.appendChild(celda);
                }

                archivo.addEventListener('change', reiniciarPreview);

                botonPreview.addEventListener('click', async function () {
                    reiniciarPreview();

                    if (! archivo.files.length) {
                        mostrarErrores(['Seleccione la planilla adicional de MinDef.']);
                        return;
                    }

                    const textoOriginal = botonPreview.innerHTML;
                    botonPreview.disabled = true;
                    botonPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Revisando...';

                    try {
                        const datosFormulario = new FormData();
                        datosFormulario.append('_token', csrf);
                        datosFormulario.append('archivo', archivo.files[0]);

                        const respuesta = await fetch(urlPreview, {
                            method: 'POST',
                            body: datosFormulario,
                            headers: { 'Accept': 'application/json' },
                        });
                        const datos = await respuesta.json();

                        if (! respuesta.ok) {
                            const mensajes = datos.errors
                                ? Object.values(datos.errors).flat()
                                : [datos.message || 'No se pudo revisar la planilla.'];
                            mostrarErrores(mensajes);
                            return;
                        }

                        hashPreview.value = datos.hash;
                        document.getElementById('previewFilasOtroArchivo').textContent = datos.filas;
                        document.getElementById('previewPrestamoOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_prestamo);
                        document.getElementById('previewComisionOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_comision);
                        document.getElementById('previewNetoOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_neto);

                        datos.registros.forEach(function (registro) {
                            const fila = document.createElement('tr');
                            agregarCelda(fila, registro.fila);
                            agregarCelda(fila, registro.papeleta);
                            agregarCelda(fila, registro.carnet);
                            agregarCelda(fila, registro.grado);
                            agregarCelda(fila, registro.nombres);
                            agregarCelda(fila, registro.destino);
                            agregarCelda(fila, moneda.format(registro.prestamo), 'text-end');
                            agregarCelda(fila, moneda.format(registro.ser_adm), 'text-end');
                            agregarCelda(fila, moneda.format(registro.neto), 'text-end');
                            tabla.appendChild(fila);
                        });

                        const notas = [];
                        if (datos.omitidas_sin_prestamo > 0) {
                            notas.push(datos.omitidas_sin_prestamo + ' fila(s) con PRESTAMO y SER ADM en cero fueron omitidas.');
                        }
                        if (datos.duplicadas.length > 0) {
                            notas.push('Papeletas ya presentes y no incorporadas: ' + datos.duplicadas.join(', ') + '.');
                        }
                        if (notas.length > 0) {
                            observaciones.textContent = notas.join(' ');
                            observaciones.classList.remove('d-none');
                        }

                        vistaPrevia.classList.remove('d-none');
                        botonConfirmar.disabled = false;
                    } catch (error) {
                        mostrarErrores(['Ocurrió un error de comunicación al revisar la planilla.']);
                    } finally {
                        botonPreview.disabled = false;
                        botonPreview.innerHTML = textoOriginal;
                    }
                });

                formulario.addEventListener('submit', function (evento) {
                    if (! hashPreview.value) {
                        evento.preventDefault();
                        mostrarErrores(['Primero debe previsualizar y revisar la planilla.']);
                        return;
                    }

                    botonConfirmar.disabled = true;
                    botonConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Incorporando...';
                });

                @if($errors->has('archivo') || $errors->has('hash_preview'))
                    bootstrap.Modal.getOrCreateInstance(modalElemento).show();
                @endif
            });
        </script>
    @endpush
@endif
