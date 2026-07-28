<x-app-layout>
    <x-slot name="header">Lotes mensuales</x-slot>
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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('procesamiento-mensual.lotes.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="mes" class="form-label">Mes</label>
                            <select name="mes" id="mes" class="form-select">
                                <option value="">Todos</option>
                                @foreach($meses as $numero => $nombre)
                                    <option
                                        value="{{ $numero }}"
                                        @selected((string) request('mes') === (string) $numero)
                                    >
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="gestion" class="form-label">Gestión</label>
                            <input
                                type="number"
                                name="gestion"
                                id="gestion"
                                value="{{ request('gestion') }}"
                                class="form-control"
                                placeholder="{{ now()->year }}"
                            >
                        </div>

                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach($estados as $estado)
                                    <option
                                        value="{{ $estado }}"
                                        @selected(request('estado') === $estado)
                                    >
                                        {{ $estado }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Buscar
                                </button>
                                <a
                                    href="{{ route('procesamiento-mensual.lotes.index') }}"
                                    class="btn btn-outline-secondary"
                                >
                                    Limpiar
                                </a>
                                <a
                                    href="{{ route('procesamiento-mensual.lotes.create') }}"
                                    class="btn btn-success ms-auto"
                                >
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Nuevo lote
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-calendar2-check text-success me-2"></i>
                    Procesamiento de lotes mensuales
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Fecha de recepción</th>
                            <th>Estado</th>
                            <th>Responsable</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $lote)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $lote->periodo }}</div>
                                    <small class="text-muted">{{ $lote->codigo_periodo }}</small>
                                </td>
                                <td>
                                    {{ $lote->fecha_recepcion?->format('d/m/Y') ?? 'Sin registrar' }}
                                </td>
                                <td>
                                    <span class="badge {{ $lote->clase_estado }}">
                                        {{ $lote->estado }}
                                    </span>
                                </td>
                                <td>
                                    {{ $lote->creador?->name ?? 'Sin identificar' }}
                                </td>
                                <td>{{ $lote->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-outline-primary btn-sm dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                        >
                                            <i class="bi bi-gear-fill me-1"></i>
                                            Acciones
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li>
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ route(
                                                        'procesamiento-mensual.lotes.show',
                                                        $lote
                                                    ) }}"
                                                >
                                                    <i class="bi bi-folder2-open me-2 text-primary"></i>
                                                    Gestionar
                                                </a>
                                            </li>
                                            @if($lote->puedeEditar())
                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="{{ route(
                                                            'procesamiento-mensual.lotes.edit',
                                                            $lote
                                                        ) }}"
                                                    >
                                                        <i class="bi bi-pencil-square me-2 text-success"></i>
                                                        Editar
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No se encontraron lotes mensuales.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lotes->hasPages())
                <div class="card-footer bg-white">
                    {{ $lotes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>