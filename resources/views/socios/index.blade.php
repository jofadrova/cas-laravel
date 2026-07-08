<x-app-layout>
    <x-slot name="header">Gestión de Socios</x-slot>
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
     @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('info') }}
            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Socios</h5>
            <a href="{{ route('socios.create') }}"
               class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Socio
            </a>
        </div>       
        <form method="GET" action="{{ route('socios.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar por</label>
                    <select name="buscar_por" class="form-select">
                        <option value="papeleta"
                            {{ request('buscar_por') == 'papeleta' ? 'selected' : '' }}>
                            Nro Papeleta
                        </option>
                        <option value="ci"
                            {{ request('buscar_por') == 'ci' ? 'selected' : '' }}>
                            CI
                        </option>

                        <option value="apellido"
                            {{ request('buscar_por') == 'apellido' ? 'selected' : '' }}>
                            Apellido
                        </option>

                        <option value="nombre"
                            {{ request('buscar_por') == 'nombre' ? 'selected' : '' }}>
                            Nombre
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Valor</label>
                    <input type="text"
                        name="valor"
                        class="form-control"
                        value="{{ request('valor') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>

                        <option value="AC"
                            {{ request('estado') == 'AC' ? 'selected' : '' }}>
                            Activos
                        </option>

                        <option value="BA"
                            {{ request('estado') == 'BA' ? 'selected' : '' }}>
                            Baja
                        </option>

                        <option value="SU"
                            {{ request('estado') == 'SU' ? 'selected' : '' }}>
                            Suspendidos
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Registros</label>
                    <select name="per_page" class="form-select">
                        <option value="10" {{ request('per_page',10)==10 ? 'selected':'' }}>10</option>
                        <option value="25" {{ request('per_page')==25 ? 'selected':'' }}>25</option>
                        <option value="50" {{ request('per_page')==50 ? 'selected':'' }}>50</option>
                        <option value="100" {{ request('per_page')==100 ? 'selected':'' }}>100</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit"
                            class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>       
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    @php
                        $currentSort = request('sort');
                        $currentDirection = request('direction', 'asc');
                    @endphp
                    <tr>
                        <th>
                            <a href="{{ route('socios.index', array_merge(request()->query(), [
                                'sort' => 'papeleta',
                                'direction' => $currentSort == 'papeleta' && $currentDirection == 'asc'
                                    ? 'desc'
                                    : 'asc'
                            ])) }}"
                            class="text-decoration-none fw-bold">
                                Papeleta
                                @if($currentSort == 'papeleta')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('socios.index', array_merge(request()->query(), [
                                'sort' => 'grado',
                                'direction' => $currentSort == 'grado' && $currentDirection == 'asc'
                                    ? 'desc'
                                    : 'asc'
                            ])) }}"
                            class="text-decoration-none fw-bold">
                                Grado
                                @if($currentSort == 'grado')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('socios.index', array_merge(request()->query(), [
                                'sort' => 'paterno',
                                'direction' => $currentSort == 'paterno' && $currentDirection == 'asc'
                                    ? 'desc'
                                    : 'asc'
                            ])) }}"
                            class="text-decoration-none fw-bold">
                                Socio
                                @if($currentSort == 'paterno')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('socios.index', array_merge(request()->query(), [
                                'sort' => 'nro_doc',
                                'direction' => $currentSort == 'nro_doc' && $currentDirection == 'asc'
                                    ? 'desc'
                                    : 'asc'
                            ])) }}"
                            class="text-decoration-none fw-bold">
                                CI
                                @if($currentSort == 'nro_doc')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('socios.index', array_merge(request()->query(), [
                                'sort' => 'estado',
                                'direction' => $currentSort == 'estado' && $currentDirection == 'asc'
                                    ? 'desc'
                                    : 'asc'
                            ])) }}"
                            class="text-decoration-none fw-bold">
                                Estado
                                @if($currentSort == 'estado')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            Opciones
                        </th>
                    </tr>
                </thead>
               <tbody>
                    @forelse($socios as $socio)
                    <tr>
                        <td><span class="fw-bold text-primary">{{ $socio->institucion?->papeleta }}</span></td>
                        <td>{{ $socio->institucion?->grado?->grado }}</td>
                        <td>
                            {{ $socio->paterno }}
                            {{ $socio->materno }}
                            {{ $socio->nombres }}
                        </td>
                        <td>
                            {{ $socio->nro_doc }}
                            {{ $socio->expedido }}
                        </td>
                        <td>
                            @if($socio->estado == 'AC')
                                <span class="badge bg-success">ACTIVO</span>
                            @elseif($socio->estado == 'BA')
                                <span class="badge bg-danger">BAJA</span>
                            @elseif($socio->estado == 'SU')
                                <span class="badge bg-warning text-dark">SUSPENDIDO</span>
                            @else
                                <span class="badge bg-secondary">{{ $socio->estado }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('socios.edit', $socio->id) }}"
                            class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>

                            <a href="{{ route('socios.show', $socio->id) }}"
                            class="btn btn-info btn-sm" title="Información del Socio"><i class="fas fa-eye"></i></a>
                            @if($socio->estado == 'AC')
                                <button class="btn btn-sm btn-danger btnCambiarEstadoSocio" title="Dar de Baja"
                                    data-id="{{ $socio->id }}"
                                    data-socio="{{ $socio->paterno }} {{ $socio->materno }} {{ $socio->nombres }}"
                                    data-estado="BA">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-success btnCambiarEstadoSocio" title="Reactivar Asociado"
                                    data-id="{{ $socio->id }}"
                                    data-socio="{{ $socio->paterno }} {{ $socio->materno }} {{ $socio->nombres }}"
                                    data-estado="AC">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            @endif
                            <a href="{{ route('socios.kardex', $socio->id) }}" class="btn btn-secondary btn-sm" title="Kardex" target="_blank"><i class="fas fa-file-pdf"></i></a>
                            @if($socio->estado == 'BA')
                                <a href="{{ route('socios.revincular', $socio->id) }}" class="btn btn-primary btn-sm"
                                title="Revincular Asociado"><i class="fas fa-rotate-right"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No existen registros</td>
                    </tr>
                    @endforelse
                    </tbody>
            </table>
            <div class="mt-3">
                {{ $socios->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
<div class="modal fade" id="modalEstadoSocio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Acción
                </h5>
            </div>

            <div class="modal-body">
                <p id="mensajeEstadoSocio"></p>
            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <form id="formEstadoSocio" method="POST">
                    @csrf
                    @method('PATCH')

                    <input type="hidden"
                           name="estado"
                           id="nuevoEstadoSocio">

                    <button type="submit"
                            class="btn btn-danger">
                        Confirmar
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>
