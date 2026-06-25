<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-file-signature me-2"></i>
        Información General
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Descripción</label>
                <input type="text" class="form-control" name="descripcion_tasa" maxlength="25" value="{{ old('descripcion_tasa', $tasa->descripcion_tasa ?? '') }}">
                @error('descripcion_tasa')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Moneda</label>
                <select class="form-select" name="tipo_moneda">
                    <option value="">- Seleccione -</option>
                    <option value="BS" {{ old('tipo_moneda', $tasa->tipo_moneda ?? '') == 'BS' ? 'selected' : '' }}>
                        Bolivianos
                    </option>
                    <option value="SU" {{ old('tipo_moneda', $tasa->tipo_moneda ?? '') == 'SU' ? 'selected' : '' }}>
                        Dólares
                    </option>
                </select>
                @error('tipo_moneda')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="AC"
                        {{ old('estado', $tasa->estado ?? 'AC') == 'AC' ? 'selected' : '' }}>
                        ACTIVO
                    </option>
                    <option value="IN"
                        {{ old('estado', $tasa->estado ?? '') == 'IN' ? 'selected' : '' }}>
                        INACTIVO
                    </option>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-money-check-dollar me-2"></i>
        Condiciones del Préstamo
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Interés (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="porcentaje" value="{{ old('porcentaje', $tasa->porcentaje ?? '') }}">
                @error('porcentaje')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Monto Máximo</label>
                <input type="number" step="0.01" min="0" class="form-control" name="monto_max" value="{{ old('monto_max', $tasa->monto_max ?? '') }}">
                @error('monto_max')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Plazo Máximo (Meses)</label>
                <input type="number" min="1" class="form-control" name="plazo_max" value="{{ old('plazo_max', $tasa->plazo_max ?? '') }}">
                @error('plazo_max')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad de Garantes</label>
                <input type="number" min="0" max="5" class="form-control" name="garante" value="{{ old('garante', $tasa->garante ?? '0') }}">
                @error('garante')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-coins me-2"></i>
        Costos Asociados
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">ITF (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="itf" value="{{ old('itf', $tasa->itf ?? '0') }}">
                @error('itf')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Interés Penal (%)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="int_penal" value="{{ old('int_penal', $tasa->int_penal ?? '0') }}">
                @error('int_penal')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Papelería</label>
                <input type="number" step="0.01" min="0" class="form-control" name="papeleria" value="{{ old('papeleria', $tasa->papeleria ?? '0') }}">
                @error('papeleria')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Ministerio de Defensa</label>
                <input type="number" step="0.01" min="0" class="form-control" name="min_defensa" value="{{ old('min_defensa', $tasa->min_defensa ?? '0') }}">
                @error('min_defensa')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-comment-dots me-2"></i>
        Observaciones
    </div>
    <div class="card-body">
        <textarea style="resize:none;" class="form-control" rows="4" name="obs">{{ old('obs', $tasa->obs ?? '') }}</textarea>
    </div>
</div>
<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('prestamos.tipos.index') }}"
       class="btn btn-secondary">
        Cancelar
    </a>
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-1"></i>
        Guardar
    </button>
</div>
