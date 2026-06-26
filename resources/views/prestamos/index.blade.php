<x-app-layout>
    <x-slot name="header">Gestión de Préstamos</x-slot>
    {{-- Mensaje --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- Buscador --}}
<form method="GET">
    <div class="row mb-3 align-items-end">
        {{-- Buscar por --}}
        <div class="col-md-2">
            <label class="form-label">Buscar por</label>
            <select name="campo" class="form-select">
                <option value="solicitud"
                    {{ request('campo', 'solicitud') == 'solicitud' ? 'selected' : '' }}>
                    Nro. Solicitud
                </option>
                <option value="papeleta"
                    {{ request('campo') == 'papeleta' ? 'selected' : '' }}>
                    Nro. Papeleta
                </option>
                <option value="asociado"
                    {{ request('campo') == 'asociado' ? 'selected' : '' }}>
                    Asociado
                </option>
            </select>
        </div>
        {{-- Criterio --}}
        <div class="col-md-3">
            <label class="form-label">Criterio</label>
            <input type="text" name="buscar" class="form-control" placeholder="Ingrese el criterio..." value="{{ request('buscar') }}">
        </div>
        {{-- Estado --}}
        <div class="col-md-2">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <option value="AC"
                    {{ request('estado') == 'AC' ? 'selected' : '' }}>
                    Activo
                </option>
                <option value="IN"
                    {{ request('estado') == 'IN' ? 'selected' : '' }}>
                    Inactivo
                </option>
            </select>
        </div>
        {{-- Tipo --}}
        <div class="col-md-3">
            <label class="form-label">Tipo de préstamo</label>
            <select name="tipo_prestamo" class="form-select">
                <option value="">Todos</option>
                @foreach($tipos as $tipo)
                    <option
                        value="{{ $tipo->id_tasa }}"
                        {{ request('tipo_prestamo') == $tipo->id_tasa ? 'selected' : '' }}>
                        {{ $tipo->descripcion_tasa }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Botones --}}
        <div class="col-md-2 d-grid gap-2">
            <button class="btn btn-primary">
                <i class="fas fa-search me-1"></i>Buscar
            </button>
            <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                Limpiar
            </a>
        </div>
    </div>
</form>
    {{-- Card Principal --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-cash-coin me-2"></i>
                Gestión de Préstamos
            </h5>
            <a href="{{ route('prestamos.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Préstamo
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Solicitud</th>
                            <th>Socio</th>
                            <th>Tipo</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Cuotas</th>
                            <th class="text-end">Saldo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestamos as $prestamo)
                            <tr>
                                <td>{{ $prestamo->nro_solicitud }}</td>
                                <td>{{ optional($prestamo->socio)->nombre_completo }}</td>
                                <td>{{ optional($prestamo->tipo)->descripcion_tasa }}</td>
                                <td class="text-end">{{ number_format($prestamo->monto,2) }}</td>
                                <td class="text-center">{{ $prestamo->ultima_cuota }}/{{ $prestamo->periodo }}</td>
                                <td class="text-end">{{ number_format($prestamo->saldo_actual,2) }}</td>
                                <td class="text-center">
                                    @if($prestamo->estado=='AC')
                                        <span class="badge bg-success">ACTIVO</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $prestamo->estado }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('prestamos.edit',$prestamo) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No existen préstamos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $prestamos->links() }}
        </div>
    </div>
</x-app-layout>
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const campo = document.querySelector('select[name="campo"]');
    const buscar = document.querySelector('input[name="buscar"]');

    function actualizarPlaceholder() {
        switch (campo.value) {
            case 'solicitud':
                buscar.placeholder = 'Ej.: 10548';
                break;

            case 'papeleta':
                buscar.placeholder = 'Ej.: 000022125';
                break;

            case 'asociado':
                buscar.placeholder = 'Nombre o apellido del asociado';
                break;

            default:
                buscar.placeholder = 'Ingrese el criterio...';
        }
    }
    actualizarPlaceholder();
    campo.addEventListener('change', actualizarPlaceholder);

});
</script>
