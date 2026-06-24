<x-app-layout>
    <x-slot name="header">Tipos de Préstamo</x-slot>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tipos de Préstamo</h5>
            <a href="#" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>
                Nuevo Tipo de Préstamo
            </a>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Descripción</th>
                        <th>Moneda</th>
                        <th>% Interés</th>
                        <th>Monto Máximo</th>
                        <th>Plazo Máximo</th>
                        <th>Estado</th>
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
                            <button class="btn btn-info btn-sm" title="Ver"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-warning btn-sm"title="Editar"><i class="fas fa-edit"></i></button>
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
