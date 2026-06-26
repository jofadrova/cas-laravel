<x-app-layout>
    <x-slot name="header">Gestión de Permisos</x-slot>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <form method="GET">

<div class="row mb-3">

<div class="col-md-5">

<input

name="buscar"

class="form-control"

placeholder="Buscar permiso..."

value="{{ request('buscar') }}">

</div>

<div class="col-md-3">

<button class="btn btn-primary">

Buscar

</button>

<a href="{{ route('permisos.index') }}"
class="btn btn-secondary">

Limpiar

</a>

</div>

</div>

</form>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa-solid fa-key me-2"></i>
                Permisos
            </h5>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPermiso">
                <i class="fa-solid fa-plus me-1"></i>Nuevo Permiso
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                       <th><a href="{{ $table->sortUrl('id') }}" class="text-decoration-none text-dark">



ID

<i class="fas {{ $table->sortIcon('id') }}"></i>

</a>

</th>
                        <th>

<a href="{{ $table->sortUrl('name') }}" class="text-decoration-none text-dark">

Permiso

<i class="fas {{ $table->sortIcon('name') }}"></i>

</a>

</th>
                        <th>Guard</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permisos as $permiso)
                        <tr>
                            <td>{{ $permiso->id }}</td>
                            <td>{{ $permiso->name }}</td>
                            <td>{{ $permiso->guard_name }}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btnEditarPermiso" data-id="{{ $permiso->id }}"data-name="{{ $permiso->name }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
    {{ $permisos->links('pagination::bootstrap-5') }}
</div>
</x-app-layout>
<!-- Modal Nuevo Permiso -->
<div class="modal fade" id="modalPermiso" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('permisos.store') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-key me-2"></i>Nuevo Permiso</h5>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nombre del Permiso</label>
                    <input type="text" name="name" class="form-control" placeholder="socios.crear" required>
                    @error('name')
                        <div class="text-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
</div>

<!-- Modal Editar Permiso -->
<div class="modal fade" id="modalEditarPermiso" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditarPermiso">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square me-2"></i>
                        Editar Permiso
                    </h5>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nombre del Permiso</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    
</div>

