<x-app-layout>
    <x-slot name="header">Separación de Certificados de Aportes · {{ $lote->periodo }}</x-slot>

    <div class="container-fluid py-4">
        @foreach(['success' => 'success', 'info' => 'info', 'error' => 'danger'] as $sesion => $clase)
            @if(session($sesion))
                <div class="alert alert-{{ $clase }} alert-dismissible fade show">
                    <i class="bi bi-{{ $clase === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-2"></i>
                    {{ session($sesion) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Resultado de la separación de aportes</h4>
                <div class="text-muted">{{ $lote->periodo }} · {{ number_format($totalRegistros) }} registros consolidados</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRecalcularAportes" @disabled(! $puedeSeparar)>
                    <i class="bi bi-arrow-repeat me-1"></i>Volver a separar
                </button>
                <a href="{{ route('procesamiento-mensual.lotes.certificados.index', $lote) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Certificados
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">Registros separados</div>
                    <div class="fs-4 fw-bold">{{ number_format((int) $resumen->registros) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">Monto total</div>
                    <div class="fs-5 fw-bold text-primary">Bs {{ number_format((float) $resumen->monto_total, 2, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">AO · Aporte obligatorio</div>
                    <div class="fs-5 fw-bold text-success">Bs {{ number_format((float) $resumen->monto_ao, 2, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">AV · Aporte voluntario</div>
                    <div class="fs-5 fw-bold text-info">Bs {{ number_format((float) $resumen->monto_av, 2, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">AI · Aporte individual</div>
                    <div class="fs-5 fw-bold text-warning">Bs {{ number_format((float) $resumen->monto_ai, 2, ',', '.') }}</div>
                </div></div>
            </div>
        </div>

        <div class="alert {{ (int) $resumen->registros === $totalRegistros ? 'alert-success' : 'alert-warning' }}">
            <i class="bi bi-shield-check me-2"></i>
            <strong>Control:</strong>
            Bs {{ number_format((float) $resumen->monto_total, 2, ',', '.') }} =
            AO Bs {{ number_format((float) $resumen->monto_ao, 2, ',', '.') }} +
            AV Bs {{ number_format((float) $resumen->monto_av, 2, ',', '.') }} +
            AI Bs {{ number_format((float) $resumen->monto_ai, 2, ',', '.') }}.
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="d-flex flex-wrap gap-2 justify-content-end">
                    <input name="buscar" value="{{ $buscar }}" class="form-control form-control-sm" style="max-width:360px" placeholder="Papeleta, carnet o nombre">
                    <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Buscar</button>
                    @if($buscar !== '')
                        <a href="{{ route('procesamiento-mensual.lotes.certificados.separacion.index', $lote) }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr><th>Archivo/Fila</th><th>Código</th><th>Papeleta</th><th>Nombre</th><th class="text-end">Total</th><th class="text-end">AO</th><th class="text-end">AV</th><th class="text-end">AI</th></tr>
                    </thead>
                    <tbody>
                        @forelse($separaciones as $separacion)
                            <tr>
                                <td>{{ $separacion->registro?->archivo?->nombre_original }} / {{ $separacion->registro?->fila_origen }}</td>
                                <td>{{ $separacion->registro?->codigo_concepto ?? '-' }}</td>
                                <td>{{ $separacion->registro?->codigo_personal_normalizado ?? '-' }}</td>
                                <td>{{ $separacion->registro?->nombres }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $separacion->monto_total, 2, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format((float) $separacion->monto_ao, 2, ',', '.') }}</td>
                                <td class="text-end text-info">{{ number_format((float) $separacion->monto_av, 2, ',', '.') }}</td>
                                <td class="text-end text-warning">{{ number_format((float) $separacion->monto_ai, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Todavía no se ejecutó la separación de aportes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($separaciones->hasPages())
                <div class="card-footer bg-white">{{ $separaciones->links() }}</div>
            @endif
        </div>
    </div>

    @if($puedeSeparar)
        <div class="modal fade" id="modalRecalcularAportes" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar nueva separación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Se recalcularán AO, AV y AI para todos los registros consolidados.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formRecalcularAportes" method="POST" action="{{ route('procesamiento-mensual.lotes.certificados.separacion.separar', $lote) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Confirmar</button>
                    </form>
                </div>
            </div></div>
        </div>
    @endif

    <div id="overlaySeparacionAportes" class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="z-index:2050;background:rgba(15,23,42,.68);">
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
            <h5>Separando aportes...</h5><p class="text-muted mb-0">Espere mientras se recalculan los certificados.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('formRecalcularAportes')?.addEventListener('submit', function () {
                const overlay = document.getElementById('overlaySeparacionAportes');
                overlay.classList.remove('d-none'); overlay.classList.add('d-flex');
                this.querySelector('button[type="submit"]').disabled = true;
            });
        </script>
    @endpush
</x-app-layout>
