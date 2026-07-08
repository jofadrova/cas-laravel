<x-app-layout>
    <x-slot name="header">Tipos de Préstamo</x-slot>
     @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tipos de Préstamo</h5>
            <a href="{{ route('prestamos.tipos.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>
                Nuevo Tipo de Préstamo
            </a>
        </div>
       <form method="GET">
            <div class="row mb-3 align-items-end">
                {{-- Buscar --}}
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" class="form-control" placeholder="Descripción..." value="{{ request('buscar') }}">
                </div>
                {{-- Moneda --}}
                <div class="col-md-2">
                    <label class="form-label">Moneda</label>
                    <select name="tipo_moneda" class="form-select">
                        <option value="">Todas</option>
                        <option value="BS"
                            {{ request('tipo_moneda') == 'BS' ? 'selected' : '' }}>
                            Bs.
                        </option>
                        <option value="SU"
                            {{ request('tipo_moneda') == 'SU' ? 'selected' : '' }}>
                            $us.
                        </option>
                    </select>
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
                {{-- Botones --}}
                <div class="col-md-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Buscar
                    </button>
                    <a href="{{ route('prestamos.tipos.index') }}" class="btn btn-secondary">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><a href="{{ $table->sortUrl('descripcion_tasa') }}" class="text-decoration-none">
                        Descripción<i class="fas {{ $table->sortIcon('descripcion_tasa') }}"></i></a>
                        </th>
                        <th><a href="{{ $table->sortUrl('tipo_moneda') }}" class="text-decoration-none">
                        Moneda<i class="fas {{ $table->sortIcon('tipo_moneda') }}"></i></a>
                        </th>
                        <th><a href="{{ $table->sortUrl('porcentaje') }}" class="text-decoration-none">
                        % Interés<i class="fas {{ $table->sortIcon('porcentaje') }}"></i></a>  
                        </th>                      
                        <th>Monto Máximo</th>
                        <th>Plazo Máximo</th>
                         <th><a href="{{ $table->sortUrl('estado') }}" class="text-decoration-none">
                        Estado<i class="fas {{ $table->sortIcon('estado') }}"></i></a>  
                        </th>   
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasas as $tasa)
                    <tr>
                        <td>{{ $tasa->descripcion_tasa }}</td>
                        <td>
                            @if($tasa->tipo_moneda == 'B')
                                Bs.
                            @elseif($tasa->tipo_moneda == 'U')
                                $us.
                            @else
                                {{ $tasa->tipo_moneda }}
                            @endif
                        </td>
                        <td>{{ number_format($tasa->porcentaje,2) }} %</td>
                        <td>{{ number_format($tasa->monto_max,2) }}</td>
                        <td>{{ $tasa->plazo_max }}</td>
                        <td>@if($tasa->estado == 'AC')
                                <span class="badge bg-success">
                                    ACTIVO
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    INACTIVO
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('prestamos.tipos.edit', $tasa) }}" class="btn btn-warning btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($tasa->estado == 'AC')
                                <button class="btn btn-danger btn-sm btnCambiarEstadoTasa" title="Desactivar"
                                    data-id="{{ $tasa->id_tasa }}"
                                    data-descripcion="{{ $tasa->descripcion_tasa }}"
                                    data-estado="IN">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @else
                                <button class="btn btn-success btn-sm btnCambiarEstadoTasa" title="Activar"
                                    data-id="{{ $tasa->id_tasa }}"
                                    data-descripcion="{{ $tasa->descripcion_tasa }}"
                                    data-estado="AC">
                                    <i class="fas fa-lock-open"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No existen registros</td>
                    </tr>
                    @endforelse
                    </tbody>
            </table>
            <div class="mt-3">
                {{ $tasas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
<div class="modal fade" id="modalEstadoTasa" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEstadoTasa">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar Acción
                    </h5>
                </div>
                <div class="modal-body">
                    <p id="mensajeEstadoTasa" class="mb-0"></p>
                    <input type="hidden" name="estado" id="nuevoEstadoTasa">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
