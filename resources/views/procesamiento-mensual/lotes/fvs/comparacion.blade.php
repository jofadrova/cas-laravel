<x-app-layout>
    <x-slot name="header">Comparación FVS · {{ $lote->periodo }}</x-slot>

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
                <h4 class="mb-1">Resultados de la comparación FVS</h4>
                <div class="text-muted">
                    {{ $lote->periodo }} ·
                    <span class="badge rounded-pill {{ $lote->clase_estado }}">{{ $lote->estado }}</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRecompararFvs"
                    @disabled(! $puedeComparar)
                >
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Volver a comparar
                </button>
                @if($procesamientoFvs)
                    <button type="button" class="btn btn-warning" disabled>
                        <i class="bi bi-hourglass-split me-1"></i>
                        PENDIENTE PARA CONTABILIDAD
                    </button>
                @else
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalFinalizarFvs"
                        @disabled(! $puedeFinalizar)
                        title="{{ $puedeFinalizar
                            ? 'Finalizar FVS y dejar el asiento pendiente para Contabilidad'
                            : 'Debe completar la comparación antes de finalizar' }}"
                    >
                        <i class="bi bi-journal-check me-1"></i>
                        Finalizar y agregar asiento contable
                    </button>
                @endif
                <a href="{{ route('procesamiento-mensual.lotes.fvs.index', $lote) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver a FVS
                </a>
            </div>
        </div>

        @if($procesamientoFvs)
            <div class="alert alert-warning border-warning shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-lg">
                        <div class="fw-bold">
                            <i class="bi bi-lock-fill me-2"></i>Procesamiento FVS finalizado
                        </div>
                        <div class="small mt-1">
                            Los archivos, la comparación y sus resultados están bloqueados para consulta.
                            El módulo contable todavía no generó el asiento.
                        </div>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <div class="text-muted small">Monto pendiente</div>
                        <div class="fw-bold">Bs {{ number_format((float) $procesamientoFvs->monto_total, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <div class="text-muted small">Estado contable</div>
                        <span class="badge bg-warning text-dark">{{ $procesamientoFvs->estado_contable }}</span>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <div class="text-muted small">Finalizado</div>
                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($procesamientoFvs->fecha_finalizacion)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">Registros consolidados</div>
                    <div class="fs-4 fw-bold">{{ number_format($resumen['total']) }}</div>
                    <div class="small text-muted">Bs {{ number_format($resumen['monto_total'], 2, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <div class="text-muted small">Registros comparados</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($resumen['comparados']) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl">
                <a href="{{ route('procesamiento-mensual.lotes.fvs.comparacion.index', ['lote' => $lote, 'estado' => 'VALIDO']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <div class="text-muted small">Descuentos válidos</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($resumen['validos']) }}</div>
                        <div class="small text-success">Bs {{ number_format($resumen['monto_valido'], 2, ',', '.') }}</div>
                    </div></div>
                </a>
            </div>
            <div class="col-sm-6 col-xl">
                <a href="{{ route('procesamiento-mensual.lotes.fvs.comparacion.index', ['lote' => $lote, 'estado' => 'NO_ENCONTRADO']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <div class="text-muted small">Socios no encontrados</div>
                        <div class="fs-4 fw-bold text-danger">{{ number_format($resumen['no_encontrados']) }}</div>
                        <div class="small text-danger">Bs {{ number_format($resumen['monto_observado'], 2, ',', '.') }}</div>
                    </div></div>
                </a>
            </div>
        </div>

        <div class="alert {{ $resumen['total'] === $resumen['comparados'] ? 'alert-success' : 'alert-warning' }}">
            <i class="bi {{ $resumen['total'] === $resumen['comparados'] ? 'bi-shield-check' : 'bi-exclamation-triangle-fill' }} me-2"></i>
            <strong>Control de integridad:</strong>
            {{ number_format($resumen['comparados']) }} de {{ number_format($resumen['total']) }} registros fueron atendidos.
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Resultado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="VALIDO" @selected($estadoSeleccionado === 'VALIDO')>Válido</option>
                            <option value="NO_ENCONTRADO" @selected($estadoSeleccionado === 'NO_ENCONTRADO')>No encontrado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">CODIGO_PERSONAL</label>
                        <input name="papeleta" value="{{ $papeletaBuscada }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre</label>
                        <input name="nombre" value="{{ $nombreBuscado }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Buscar</button>
                        <a href="{{ route('procesamiento-mensual.lotes.fvs.comparacion.index', $lote) }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Archivo/Fila</th><th>CODIGO_PERSONAL</th><th>Nombre en Excel</th>
                            <th class="text-end">Descuento</th><th>Resultado</th><th>Papeleta encontrada</th>
                            <th>Asociado</th><th>CI</th><th>Estado socio</th><th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td>{{ $registro->archivo?->nombre_original }} / {{ $registro->fila_origen }}</td>
                                <td class="fw-semibold">{{ $registro->codigo_personal_normalizado ?? '-' }}</td>
                                <td>{{ $registro->nombres }}</td>
                                <td class="text-end">Bs {{ number_format((float) $registro->monto_descuento, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $registro->estado === 'VALIDO' ? 'bg-success' : ($registro->estado === 'NO_ENCONTRADO' ? 'bg-danger' : 'bg-secondary') }}">
                                        {{ str_replace('_', ' ', $registro->estado) }}
                                    </span>
                                </td>
                                <td>{{ $registro->socioInstitucion?->papeleta ?? '-' }}</td>
                                <td>
                                    {{ $registro->socio
                                        ? trim($registro->socio->paterno.' '.$registro->socio->materno.' '.$registro->socio->nombres)
                                        : '-' }}
                                </td>
                                <td>{{ $registro->socio?->nro_doc ?? '-' }}</td>
                                <td>{{ $registro->socio?->estado ?? '-' }}</td>
                                <td class="text-wrap" style="min-width: 260px;">{{ $registro->observacion }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No existen resultados con los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($registros->hasPages())
                <div class="card-footer bg-white">{{ $registros->links() }}</div>
            @endif
        </div>
    </div>

    @if($puedeComparar)
        <div class="modal fade" id="modalRecompararFvs" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar nueva comparación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Se volverán a validar todos los registros FVS y se reemplazarán los resultados actuales.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formRecompararFvs" method="POST" action="{{ route('procesamiento-mensual.lotes.fvs.comparacion.comparar', $lote) }}">
                        @csrf
                        <button class="btn btn-danger" type="submit">Confirmar</button>
                    </form>
                </div>
            </div></div>
        </div>
    @endif

    @if($puedeFinalizar)
        <div class="modal fade" id="modalFinalizarFvs" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Confirmar finalización FVS
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea finalizar el procesamiento FVS del lote {{ $lote->periodo }}?</p>
                        <div class="border rounded-3 bg-light p-3 mb-3">
                            <div class="d-flex justify-content-between"><span>Descuentos válidos</span><strong>{{ number_format($resumen['validos']) }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Monto total para Contabilidad</span><strong>Bs {{ number_format($resumen['monto_total'], 2, ',', '.') }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Registros observados</span><strong>{{ number_format($resumen['no_encontrados']) }}</strong></div>
                        </div>
                        <div class="alert alert-warning mb-0">
                            Esta acción bloqueará las cargas, eliminaciones y nuevas comparaciones FVS.
                            No se generará todavía un asiento: el monto total, incluidos los
                            registros observados, quedará con estado
                            <strong>PENDIENTE PARA CONTABILIDAD</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form id="formFinalizarFvs" method="POST" action="{{ route('procesamiento-mensual.lotes.fvs.comparacion.finalizar', $lote) }}">
                            @csrf
                            <button class="btn btn-danger" type="submit">
                                <i class="bi bi-lock-fill me-1"></i>Confirmar finalización
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="overlayComparacionFvs" class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="z-index:2050;background:rgba(15,23,42,.68);">
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
            <h5>Procesando y comparando...</h5>
            <p class="text-muted mb-0">Espere mientras se valida el padrón de asociados.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('formRecompararFvs')?.addEventListener('submit', function () {
                document.getElementById('overlayComparacionFvs').classList.remove('d-none');
                document.getElementById('overlayComparacionFvs').classList.add('d-flex');
                this.querySelector('button[type="submit"]').disabled = true;
            });
            document.getElementById('formFinalizarFvs')?.addEventListener('submit', function () {
                const overlay = document.getElementById('overlayComparacionFvs');
                overlay.querySelector('h5').textContent = 'Finalizando FVS...';
                overlay.querySelector('p').textContent = 'Registrando el monto pendiente para Contabilidad.';
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
                this.querySelector('button[type="submit"]').disabled = true;
            });
        </script>
    @endpush
</x-app-layout>
