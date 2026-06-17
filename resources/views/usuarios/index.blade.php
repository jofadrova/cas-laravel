<x-app-layout>
    <x-slot name="header">
        Usuarios
    </x-slot>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Gestión de Usuarios</h5>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario"><i class="bi bi-plus-circle me-1"></i> Nuevo Usuario </button>
        </div>

        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Rol</th>
                        <th class="text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->username }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>
                                @if($usuario->estado == 'ACTIVO')
                                    <span class="badge bg-success">ACTIVO</span>
                                @else
                                    <span class="badge bg-danger">INACTIVO</span>
                                @endif
                            </td>
                            <td>
                                @if($usuario->roles->count())
                                    <span class="badge bg-primary">{{ $usuario->roles->first()->name }}</span>
                                @else
                                    <span class="badge bg-secondary">* Sin Rol *</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- Editar -->
                                <button class="btn btn-sm btn-warning me-1 btnEditarUsuario"
                                    data-id="{{ $usuario->id }}"
                                    data-username="{{ $usuario->username }}"
                                    data-name="{{ $usuario->name }}"
                                    data-email="{{ $usuario->email }}"
                                    data-estado="{{ $usuario->estado }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- Password -->
                                <button class="btn btn-sm btn-info me-1 btnResetPassword" title="Restablecer Contraseña"
                                    data-id="{{ $usuario->id }}"
                                    data-usuario="{{ $usuario->username }}">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                                <!-- Estado -->
                               @if($usuario->estado == 'ACTIVO')
                                    <button class="btn btn-sm btn-danger btnCambiarEstado" title="Desactivar Usuario"
                                        data-id="{{ $usuario->id }}"
                                        data-usuario="{{ $usuario->username }}"
                                        data-estado="INACTIVO">
                                        <i class="fas fa-user-lock"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-success btnCambiarEstado" title="Activar Usuario" data-id="{{ $usuario->id }}"
                                        data-usuario="{{ $usuario->username }}"
                                        data-estado="ACTIVO">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $usuarios->links() }}
        </div>
    </div>
</x-app-layout>
<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" class="form-control" required value="{{ old('username') }}">
                            @error('username')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                            @error('email')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="ACTIVO" {{ old('estado') == 'ACTIVO' ? 'selected' : '' }}>
                                    ACTIVO
                                </option>
                                <option value="INACTIVO">
                                    INACTIVO
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                            <div id="mensajePassword" class="text-danger small mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancelar</button>
                    <button type="submit" id="btnGuardarUsuario" class="btn btn-success"> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="formEditarUsuario">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Editar Usuario
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" id="edit_username" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="edit_estado" class="form-select">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"> Actualizar </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEstadoUsuario" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEstadoUsuario">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar Acción
                    </h5>
                </div>
                <div class="modal-body">
                    <p id="mensajeEstado" class="mb-0"></p>
                    <input type="hidden" name="estado" id="nuevoEstado">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Restablecer Contraseña -->
<div class="modal fade"
     id="modalPassword"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" id="formPassword">
                <input type="hidden" id="password_user_id" value="{{ session('reset_password_user.id') }}">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-key me-2"></i>
                        Restablecer Contraseña
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">
                            Usuario
                        </label>
                       <input type="text" id="password_username" class="form-control" readonly value="{{ session('reset_password_user.username') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nueva Contraseña
                        </label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Confirmar Contraseña
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        <div id="mensajePasswordReset" class="small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancelar</button>
                    <button type="submit" id="btnGuardarPassword" class="btn btn-info"> Actualizar </button>
                </div>
            </form>
        </div>
    </div>
</div>
@if ($errors->any())
<input type="hidden" id="abrirModalUsuario">
@endif
@if ($errors->resetPassword->any())
    <input type="hidden" id="abrirModalPassword">
@endif
