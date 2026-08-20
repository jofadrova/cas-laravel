<x-app-layout>
    <x-slot name="header">Envíos mensuales</x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('procesamiento-mensual.envios-mensuales.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="gestion" class="form-label">Gestión</label>
                            <input type="number" name="gestion" id="gestion" value="{{ request('gestion') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Buscar</button>
                            <a href="{{ route('procesamiento-mensual.envios-mensuales.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                            <a href="{{ route('procesamiento-mensual.envios-mensuales.create') }}" class="btn btn-success ms-auto"><i class="bi bi-plus-circle me-1"></i>Nuevo lote de envío</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-send text-success me-2"></i>Lotes de envío</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Código</th><th>Periodo</th><th>Destinatario</th><th>Estado</th><th>Responsable</th><th class="text-end">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse($envios as $envio)
                            <tr>
                                <td class="fw-semibold">{{ $envio->codigo }}</td>
                                <td>{{ $envio->periodo }}</td>
                                <td>{{ $envio->destinatario }}</td>
                                <td><span class="badge {{ $envio->clase_estado }}">{{ $envio->estado }}</span></td>
                                <td>{{ $envio->creador?->name ?? 'Sin identificar' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('procesamiento-mensual.envios-mensuales.show', $envio) }}"><i class="bi bi-folder2-open me-1"></i>Gestionar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No se encontraron lotes de envío.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($envios->hasPages())<div class="card-footer bg-white">{{ $envios->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
