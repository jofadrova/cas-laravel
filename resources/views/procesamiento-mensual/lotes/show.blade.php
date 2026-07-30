<x-app-layout>
    <x-slot name="header">
        Lote {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">{{ $lote->periodo }}</h4>
                <div class="text-muted">Código de periodo: {{ $lote->codigo_periodo }}</div>
            </div>
            <div class="d-flex gap-2">
                <a
                    href="{{ route('procesamiento-mensual.lotes.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver
                </a>
                @if($lote->puedeEditar())
                    <a
                        href="{{ route('procesamiento-mensual.lotes.edit', $lote) }}"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-pencil-square me-1"></i>
                        Editar lote
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Información del lote
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-muted small">Mes</div>
                                <div class="fw-semibold">{{ $lote->nombre_mes }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Gestión</div>
                                <div class="fw-semibold">{{ $lote->gestion }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Estado</div>
                                <span class="badge rounded-pill {{ $lote->clase_estado }}">
                                    {{ $lote->estado ?: 'SIN ESTADO' }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Fecha de recepción</div>
                                <div class="fw-semibold">
                                    {{ $lote->fecha_recepcion?->format('d/m/Y') ?? 'Sin registrar' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Tipo de cambio</div>
                                <div class="fw-semibold">
                                    @if($lote->tipo_cambio)
                                        $us 1 = Bs {{ number_format((float) $lote->tipo_cambio, 5, ',', '.') }}
                                    @else
                                        Sin registrar
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Creado por</div>
                                <div class="fw-semibold">
                                    {{ $lote->creador?->name ?? 'Sin identificar' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Fecha de creación</div>
                                <div class="fw-semibold">
                                    {{ $lote->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Observaciones</div>
                                <div class="fw-semibold">
                                    {{ $lote->observaciones ?: 'Sin observaciones.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-folder2-open text-success me-2"></i>
                            Operaciones del lote
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            El lote está listo para incorporar sus archivos mensuales.
                        </div>
                        <a
                            href="{{ route('procesamiento-mensual.lotes.archivos.index', ['lote' => $lote]) }}"
                            class="btn btn-success w-100"
                        >
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                            Gestionar Préstamos
                        </a>
                        <small class="text-muted d-block mt-2">
                            Cargue, compare y consolide los descuentos mensuales de préstamos.
                        </small>

                        <a
                            href="{{ route('procesamiento-mensual.lotes.fvs.index', ['lote' => $lote]) }}"
                            class="btn btn-success w-100 mt-3"
                        >
                            <i class="bi bi-currency-exchange me-1"></i>
                            Gestionar FVS
                        </a>
                        <small class="text-muted d-block mt-2">
                            Cargue y consolide los archivos mensuales correspondientes a FVS.
                        </small>

                        <a
                            href="{{ route('procesamiento-mensual.lotes.certificados.index', ['lote' => $lote]) }}"
                            class="btn btn-success w-100 mt-3"
                        >
                            <i class="bi bi-award me-1"></i>
                            Gestionar Certificados de Aportes
                        </a>
                        <small class="text-muted d-block mt-2">
                            Cargue y consolide los archivos mensuales de certificados de aportes.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
