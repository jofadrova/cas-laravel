<x-app-layout>
    <x-slot name="header">Nomenclatura de cuentas</x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row mb-3 align-items-end g-3">
        <div class="col-md-4">
            <label class="form-label" for="buscar">Buscar</label>
            <input class="form-control" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Código o nombre de cuenta">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="tipo">Rubro</label>
            <select class="form-select" id="tipo" name="tipo">
                <option value="">Todos</option>
                @foreach(['ACTIVO' => 'Activo', 'PASIVO' => 'Pasivo', 'PATRIMONIO' => 'Patrimonio', 'INGRESO' => 'Ingreso', 'GASTO' => 'Gasto', 'ORDEN' => 'Cuentas de orden'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="estado">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="">Todos</option>
                <option value="1" @selected(request('estado') === '1')>Activo</option>
                <option value="0" @selected(request('estado') === '0')>Inactivo</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Buscar</button>
            <a class="btn btn-secondary" href="{{ route('contabilidad.cuentas.index') }}">Limpiar</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Plan de cuentas</h5>
                <small class="text-muted">Estructura jerárquica y cuentas habilitadas para movimiento</small>
            </div>
            <a class="btn btn-success" href="{{ route('contabilidad.cuentas.create') }}"><i class="bi bi-plus-circle me-1"></i>Nueva cuenta</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Código / cuenta</th><th>Rubro</th><th>Naturaleza</th><th>Moneda</th><th>Uso</th><th>Estado</th><th class="text-center">Opciones</th></tr>
                    </thead>
                    <tbody>
                    @forelse($cuentas as $cuenta)
                        <tr>
                            <td>
                                <div style="padding-left: {{ max(0, $cuenta->nivel - 1) * 1.25 }}rem">
                                    <span class="fw-semibold">{{ $cuenta->codigo }}</span> · {{ $cuenta->nombre }}
                                    @if($cuenta->padre)<div class="small text-muted">Depende de {{ $cuenta->padre->codigo }}</div>@endif
                                </div>
                            </td>
                            <td>{{ ucfirst(strtolower($cuenta->tipo)) }}</td>
                            <td>{{ $cuenta->naturaleza === 'D' ? 'Deudora' : 'Acreedora' }}</td>
                            <td>{{ ['B' => 'Bs.', 'U' => '$us.', 'M' => 'Multi'][$cuenta->moneda] ?? $cuenta->moneda }}</td>
                            <td><span class="badge {{ $cuenta->acepta_movimientos ? 'bg-primary' : 'bg-secondary' }}">{{ $cuenta->acepta_movimientos ? 'Movimiento' : 'Agrupadora' }}</span></td>
                            <td><span class="badge {{ $cuenta->estado ? 'bg-success' : 'bg-danger' }}">{{ $cuenta->estado ? 'ACTIVO' : 'INACTIVO' }}</span></td>
                            <td class="text-center text-nowrap">
                                <a class="btn btn-warning btn-sm" href="{{ route('contabilidad.cuentas.edit', $cuenta) }}" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form class="d-inline" method="POST" action="{{ route('contabilidad.cuentas.estado', $cuenta) }}" onsubmit="return confirm('¿Confirma el cambio de estado de esta cuenta?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ $cuenta->estado ? 0 : 1 }}">
                                    <button class="btn btn-{{ $cuenta->estado ? 'danger' : 'success' }} btn-sm" title="{{ $cuenta->estado ? 'Inactivar' : 'Activar' }}"><i class="bi bi-{{ $cuenta->estado ? 'lock' : 'unlock' }}"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No existen cuentas registradas con los filtros seleccionados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($cuentas->hasPages())<div class="card-footer">{{ $cuentas->links() }}</div>@endif
    </div>
</x-app-layout>
