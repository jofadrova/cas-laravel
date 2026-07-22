<x-app-layout>
    <x-slot name="header">Gestión de Socios</x-slot>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"> </button>
        </div>
    @endif
     @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close"  data-bs-dismiss="alert"></button>
        </div>
    @endif
    <form method="GET" action="{{ route('socios.index') }}">
        <div class="row mb-3 align-items-end">
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
                <input type="text" name="valor" class="form-control" value="{{ request('valor') }}">
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
                <button type="submit" class="btn btn-primary w-100"> 
                    <i class="fas fa-search me-1"></i>
                    Buscar
                </button>
            </div>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Gestión de Socios</h5>
            <a href="{{ route('socios.create') }}"
               class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Nuevo Socio
            </a>
        </div>
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
                            class="text-dark text-decoration-none fw-bold">
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
                            ])) }}" class="text-dark text-decoration-none fw-bold">
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
                                    : 'asc' ])) }}"
                            class="text-dark text-decoration-none fw-bold">
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
                                    : 'asc' ])) }}"
                            class="text-dark text-decoration-none fw-bold">
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
                                    : 'asc' ])) }}"
                            class="text-dark text-decoration-none fw-bold">
                                Estado
                                @if($currentSort == 'estado')
                                    <i class="fas fa-sort-{{ $currentDirection == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="fas fa-sort text-muted ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            Acciones
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
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill me-1"></i>
                                    Acciones
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item"
                                        href="{{ route('socios.edit', $socio->id) }}">
                                            <i class="fas fa-edit me-2 text-warning"></i>
                                            Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item btnInformacionSocio"
                                        href="{{ route('socios.show', $socio->id) }}">
                                            <i class="fas fa-eye me-2 text-info"></i>
                                            Información del Socio
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item btnCambiarEstadoSocio"
                                                data-id="{{ $socio->id }}"
                                                data-socio="{{ $socio->paterno }} {{ $socio->materno }} {{ $socio->nombres }}"
                                                data-estado="{{ $socio->estado == 'AC' ? 'BA' : 'AC' }}">
                                            @if($socio->estado == 'AC')
                                                <i class="fas fa-user-slash me-2 text-danger"></i>
                                                Dar de Baja
                                            @else
                                                <i class="fas fa-user-check me-2 text-success"></i>
                                                Reactivar Asociado
                                            @endif
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                        href="{{ route('socios.kardex', $socio->id) }}"
                                        target="_blank">
                                            <i class="fas fa-file-pdf me-2 text-secondary"></i>
                                            Kardex
                                        </a>
                                    </li>
                                    @if($socio->estado == 'BA')
                                        <li><hr class="dropdown-divider"></li>

                                        <li>
                                            <a class="dropdown-item"
                                            href="{{ route('socios.revincular', $socio->id) }}">
                                                <i class="fas fa-rotate-right me-2 text-primary"></i>
                                                Revincular Asociado
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
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
    <div class="modal fade" id="modalInformacionSocio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header scas-header text-white py-2">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Información del Socio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3" id="contenidoInformacionSocio">
                    <div class="text-center py-5 text-muted">
                        Seleccione un socio para consultar su información.
                    </div>
                </div>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form id="formEstadoSocio" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="estado" id="nuevoEstadoSocio">
                    <button type="submit" class="btn btn-danger">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
