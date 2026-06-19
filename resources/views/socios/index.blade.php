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
                            class="btn btn-warning btn-sm"
                            title="Editar"><i class="fas fa-edit"></i></a>
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
