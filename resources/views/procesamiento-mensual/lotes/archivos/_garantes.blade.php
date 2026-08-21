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
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($lote->envioMensual?->archivoGarantes)
            <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-3 mb-0">
                <div>
                    <div class="fw-semibold">
                        <i class="bi bi-link-45deg me-1"></i>
                        Garantes vinculados al envío {{ $lote->envioMensual->codigo }}
                    </div>
                    <div>{{ $lote->envioMensual->archivoGarantes->nombre_original }}</div>
                    <div class="small">
                        {{ number_format($lote->envioMensual->archivoGarantes->cantidad_registros) }} registros ·
                        Bs {{ number_format((float) $lote->envioMensual->archivoGarantes->monto_total, 2, '.', ',') }}
                    </div>
                    <div class="small mt-1">
                        No debe volver a subir este archivo. Sus datos se incorporaron automáticamente
                        al registrar la recepción y, al continuar, se verificarán contra los pagos recibidos.
                    </div>
                </div>
                <a class="btn btn-outline-success btn-sm" href="{{ route('procesamiento-mensual.envios-mensuales.garantes.descargar', $lote->envioMensual) }}">
                    <i class="bi bi-download me-1"></i>Descargar Excel de garantes
                </a>
            </div>
        @else
            <div class="alert alert-danger mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                El envío relacionado no tiene disponible el Excel obligatorio de garantes.
                No se podrá procesar Préstamos hasta corregir el envío.
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
