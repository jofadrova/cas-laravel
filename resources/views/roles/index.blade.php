<x-app-layout>
    <x-slot name="header">Gestión de Roles</x-slot>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>
                Roles
            </h5>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRol"><i class="bi bi-plus-circle me-1"></i>
                Nuevo Rol
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">ID</th>
                            <th>Rol</th>
                            <th width="120">Usuarios</th>
                            <th width="150" class="text-center">
                                Opciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $rol)
                            <tr>
                                <td>{{ $rol->id }}</td>
                                <td><strong>{{ $rol->name }}</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $rol->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    <!-- Editar -->
                                    <button class="btn btn-sm btn-warning me-1 btnEditarRol" data-id="{{ $rol->id }}"
                                        data-name="{{ $rol->name }}"
                                        title="Editar Rol">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <!-- Usuarios -->
                                    <button class="btn btn-sm btn-info btnUsuariosRol" title="Asignar Usuarios"
                                        data-id="{{ $rol->id }}"
                                        data-name="{{ $rol->name }}"><i class="fa-solid fa-users"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No existen roles registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<!-- Modal Nuevo Rol -->
<div class="modal fade" id="modalRol" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus me-2"></i>Nuevo Rol
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Rol</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Editar Rol -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" data-bs-backdrop="static"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditarRol">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">

                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square me-2"></i>
                        Editar Rol
                    </h5>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">

                            Nombre del Rol

                        </label>

                        <input
                            type="text"
                            id="edit_name"
                            name="name"
                            class="form-control"
                            required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        Actualizar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<div class="modal fade"
     id="modalUsuariosRol"
     tabindex="-1"
     data-bs-backdrop="static">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form method="POST"
                  id="formUsuariosRol">

                @csrf

                <div class="modal-header bg-info text-white">

                    <h5 class="modal-title">

                        <i class="fa-solid fa-users me-2"></i>

                        Usuarios del Rol

                    </h5>

                </div>

                <div class="modal-body">

                    <div class="alert alert-info mb-3">

                        <strong>Rol:</strong>

                        <span id="nombreRol"></span>

                    </div>

                    <div id="listaUsuarios">

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-info">

                        Guardar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>