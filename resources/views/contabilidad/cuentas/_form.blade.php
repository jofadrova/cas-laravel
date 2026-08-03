@php($cuenta = $cuenta ?? null)
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="cuenta_padre_id">Cuenta superior</label>
        <select class="form-select @error('cuenta_padre_id') is-invalid @enderror" id="cuenta_padre_id" name="cuenta_padre_id">
            <option value="">Sin cuenta superior (nivel 1)</option>
            @foreach($padres as $padre)
                <option value="{{ $padre->id }}" @selected((string) old('cuenta_padre_id', $cuenta?->cuenta_padre_id) === (string) $padre->id)>
                    {{ str_repeat('— ', max(0, $padre->nivel - 1)) }}{{ $padre->codigo }} · {{ $padre->nombre }}
                </option>
            @endforeach
        </select>
        @error('cuenta_padre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Solo aparecen cuentas activas que no reciben movimientos.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="codigo">Código <span class="text-danger">*</span></label>
        <input class="form-control text-uppercase @error('codigo') is-invalid @enderror" id="codigo" name="codigo" value="{{ old('codigo', $cuenta?->codigo) }}" placeholder="Ej. 1.01.001">
        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label" for="nombre">Nombre de la cuenta <span class="text-danger">*</span></label>
        <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $cuenta?->nombre) }}">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="tipo">Rubro <span class="text-danger">*</span></label>
        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo">
            @foreach(['ACTIVO' => 'Activo', 'PASIVO' => 'Pasivo', 'PATRIMONIO' => 'Patrimonio', 'INGRESO' => 'Ingreso', 'GASTO' => 'Gasto', 'ORDEN' => 'Cuentas de orden'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('tipo', $cuenta?->tipo) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="naturaleza">Naturaleza <span class="text-danger">*</span></label>
        <select class="form-select @error('naturaleza') is-invalid @enderror" id="naturaleza" name="naturaleza">
            <option value="D" @selected(old('naturaleza', $cuenta?->naturaleza) === 'D')>Deudora</option>
            <option value="A" @selected(old('naturaleza', $cuenta?->naturaleza) === 'A')>Acreedora</option>
        </select>
        @error('naturaleza')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="moneda">Moneda <span class="text-danger">*</span></label>
        <select class="form-select @error('moneda') is-invalid @enderror" id="moneda" name="moneda">
            <option value="B" @selected(old('moneda', $cuenta?->moneda ?? 'B') === 'B')>Bolivianos</option>
            <option value="U" @selected(old('moneda', $cuenta?->moneda) === 'U')>Dólares estadounidenses</option>
            <option value="M" @selected(old('moneda', $cuenta?->moneda) === 'M')>Multimoneda</option>
        </select>
        @error('moneda')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="acepta_movimientos" value="0">
            <input class="form-check-input" id="acepta_movimientos" name="acepta_movimientos" type="checkbox" value="1" @checked(old('acepta_movimientos', $cuenta?->acepta_movimientos ?? false))>
            <label class="form-check-label" for="acepta_movimientos">Recibe movimientos</label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="vigente_desde">Vigente desde</label>
        <input class="form-control @error('vigente_desde') is-invalid @enderror" id="vigente_desde" name="vigente_desde" type="date" value="{{ old('vigente_desde', $cuenta?->vigente_desde?->format('Y-m-d')) }}">
        @error('vigente_desde')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="vigente_hasta">Vigente hasta</label>
        <input class="form-control @error('vigente_hasta') is-invalid @enderror" id="vigente_hasta" name="vigente_hasta" type="date" value="{{ old('vigente_hasta', $cuenta?->vigente_hasta?->format('Y-m-d')) }}">
        @error('vigente_hasta')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="referencia_normativa">Referencia normativa</label>
        <input class="form-control @error('referencia_normativa') is-invalid @enderror" id="referencia_normativa" name="referencia_normativa" value="{{ old('referencia_normativa', $cuenta?->referencia_normativa) }}" placeholder="MCEF, capítulo o resolución aplicable">
        @error('referencia_normativa')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="descripcion">Descripción y reglas de uso</label>
        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $cuenta?->descripcion) }}</textarea>
        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-secondary" href="{{ route('contabilidad.cuentas.index') }}">Cancelar</a>
        <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>{{ $cuenta ? 'Actualizar cuenta' : 'Registrar cuenta' }}</button>
    </div>
</div>
