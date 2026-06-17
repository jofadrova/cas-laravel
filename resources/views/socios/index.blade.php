<x-app-layout>

    <x-slot name="header">
        Gestión de Socios
    </x-slot>

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

        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">
                Gestión de Socios
            </h5>
            <a href="{{ route('socios.create') }}"
               class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Socio
            </a>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Papeleta</th>
                        <th>Grado</th>
                        <th>Socio</th>
                        <th>CI</th>
                        <th>Estado</th>
                        <th class="text-center">
                            Opciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Temporal hasta conectar BD --}}
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No existen registros
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
