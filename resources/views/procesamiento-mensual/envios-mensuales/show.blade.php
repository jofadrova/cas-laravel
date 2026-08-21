<x-app-layout>
    <x-slot name="header">Lote de envío {{ $envio->codigo }}</x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>@endif
        @if($errors->has('prestamos'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('prestamos') }}</div>@endif
        @if($errors->has('archivo_garantes'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('archivo_garantes') }}</div>@endif
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div><h5 class="mb-1">{{ $envio->codigo }}</h5><small class="text-muted">{{ $envio->periodo }}</small></div>
                <a href="{{ route('procesamiento-mensual.envios-mensuales.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3"><div class="text-muted small">Estado</div><span class="badge {{ $envio->clase_estado }}">{{ $envio->estado }}</span></div>
                    <div class="col-md-3"><div class="text-muted small">Destinatario</div><div class="fw-semibold">{{ $envio->destinatario }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">Responsable</div><div class="fw-semibold">{{ $envio->creador?->name ?? 'Sin identificar' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">Creado</div><div class="fw-semibold">{{ $envio->created_at?->format('d/m/Y H:i') }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">Fecha de envío</div><div class="fw-semibold">{{ $envio->fecha_envio?->format('d/m/Y') ?? 'Pendiente' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">Recepción</div><div class="fw-semibold">{{ $envio->loteMensual ? 'Registrada' : 'Pendiente' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Observaciones</div><div>{{ $envio->observaciones ?: 'Sin observaciones.' }}</div></div>
                </div>
                <hr class="my-4">
                <div class="card border rounded-3 mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="text-uppercase text-muted small fw-semibold mb-1">Paso 1</div>
                                <h5 class="mb-2">Generar TXT de préstamos y garantes</h5>
                                <p class="text-muted mb-2">
                                    Genera el TXT para {{ $envio->periodo }} con la estructura oficial de 8 campos,
                                    colocando primero los préstamos y al final los descuentos del Excel de garantes.
                                </p>
                                <div class="small">
                                    <span class="me-3"><strong>{{ number_format($resumenPrestamos['cantidad']) }}</strong> préstamos encontrados</span>
                                    @if($resumenPrestamos['tipos_cambio_invalidos'] > 0)
                                        <span class="text-danger"><strong>{{ number_format($resumenPrestamos['tipos_cambio_invalidos']) }}</strong> sin tipo de cambio válido</span>
                                    @elseif($resumenPrestamos['datos_invalidos'] > 0)
                                        <span class="text-danger"><strong>{{ number_format($resumenPrestamos['datos_invalidos']) }}</strong> con datos institucionales inválidos</span>
                                    @else
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>Datos listos para generar</span>
                                    @endif
                                </div>
                                @if($envio->archivoGarantes)
                                    <div class="small text-success mt-2">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        {{ $envio->archivoGarantes->nombre_original }} ·
                                        {{ number_format($envio->archivoGarantes->cantidad_registros) }} garantes ·
                                        Bs {{ number_format((float) $envio->archivoGarantes->monto_total, 2, '.', ',') }}
                                    </div>
                                @endif
                            </div>
                            @if(in_array($envio->estado, [\App\Models\EnvioMensual::ESTADO_BORRADOR, \App\Models\EnvioMensual::ESTADO_PREPARANDO, \App\Models\EnvioMensual::ESTADO_VALIDADO], true))
                                <form
                                    method="POST"
                                    action="{{ route('procesamiento-mensual.envios-mensuales.prestamos.generar', $envio) }}"
                                    enctype="multipart/form-data"
                                    class="align-self-lg-center"
                                    data-confirm-title="{{ $envio->archivoPrestamos ? 'Confirmar regeneración del TXT' : 'Confirmar generación del TXT' }}"
                                    data-confirm-message="El TXT se generará con los préstamos actuales y los garantes del Excel para {{ $envio->periodo }}. Los garantes quedarán al final del mismo archivo."
                                    data-confirm-button="{{ $envio->archivoPrestamos ? 'Regenerar TXT' : 'Generar TXT' }}"
                                >
                                    @csrf
                                    <label for="archivo_garantes" class="form-label small fw-semibold">
                                        Excel de garantes <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        id="archivo_garantes"
                                        name="archivo_garantes"
                                        class="form-control form-control-sm mb-2 @error('archivo_garantes') is-invalid @enderror"
                                        accept=".xlsx,.xls"
                                        required
                                    >
                                    <div class="form-text mb-2">Obligatorio en cada generación o regeneración del TXT.</div>
                                    <button type="submit" class="btn btn-primary w-100" @disabled($resumenPrestamos['cantidad'] === 0 || $resumenPrestamos['tipos_cambio_invalidos'] > 0 || $resumenPrestamos['datos_invalidos'] > 0)>
                                        <i class="bi bi-file-earmark-text me-1"></i>{{ $envio->archivoPrestamos ? 'Regenerar TXT' : 'Generar TXT' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if($envio->archivoPrestamos)
                            <hr>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <div class="text-muted small">Archivo generado</div>
                                    <div class="fw-semibold text-break">{{ $envio->archivoPrestamos->nombre_original }}</div>
                                    <div class="small text-muted">{{ $envio->archivoPrestamos->generado_en?->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small">Registros</div>
                                    <div class="fw-semibold">{{ number_format($envio->archivoPrestamos->cantidad_registros) }}</div>
                                    @if($envio->archivoGarantes)
                                        <div class="small text-muted">
                                            {{ number_format($envio->archivoPrestamos->cantidad_registros - $envio->archivoGarantes->cantidad_registros) }} préstamos +
                                            {{ number_format($envio->archivoGarantes->cantidad_registros) }} garantes
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-2"><div class="text-muted small">Monto total</div><div class="fw-semibold">Bs {{ number_format((float) $envio->archivoPrestamos->monto_total, 2, '.', ',') }}</div></div>
                                <div class="col-md-3 text-md-end">
                                    <a href="{{ route('procesamiento-mensual.envios-mensuales.prestamos.descargar', $envio) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Descargar TXT
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @if(in_array($envio->estado, [\App\Models\EnvioMensual::ESTADO_BORRADOR, \App\Models\EnvioMensual::ESTADO_PREPARANDO, \App\Models\EnvioMensual::ESTADO_VALIDADO], true))
                    <form
                        method="POST"
                        action="{{ route('procesamiento-mensual.envios-mensuales.marcar-enviado', $envio) }}"
                        data-confirm-title="Confirmar envío mensual"
                        data-confirm-message="El lote {{ $envio->codigo }} se marcará como enviado a MINDEF y quedará habilitado para registrar su recepción."
                        data-confirm-button="Marcar como enviado"
                    >
                        @csrf
                        <button type="submit" class="btn btn-primary" @disabled(! $envio->archivoPrestamos || ! $envio->archivoGarantes)><i class="bi bi-send-check me-1"></i>Marcar como enviado</button>
                    </form>
                @elseif($envio->estado === \App\Models\EnvioMensual::ESTADO_ENVIADO && ! $envio->loteMensual)
                    <a href="{{ route('procesamiento-mensual.lotes.create', ['envio_mensual_id' => $envio->id]) }}" class="btn btn-success"><i class="bi bi-inbox-arrow-down me-1"></i>Registrar recepción</a>
                @elseif($envio->loteMensual)
                    <a href="{{ route('procesamiento-mensual.lotes.show', $envio->loteMensual) }}" class="btn btn-outline-primary"><i class="bi bi-folder2-open me-1"></i>Abrir lote recibido y procesarlo</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
