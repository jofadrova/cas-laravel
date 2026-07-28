@php
    $esEdicion = isset($lote);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="mes" class="form-label">
            Mes <span class="text-danger">*</span>
        </label>
        <select
            name="mes"
            id="mes"
            class="form-select @error('mes') is-invalid @enderror"
            required
        >
            <option value="">Seleccione...</option>
            @foreach($meses as $numero => $nombre)
                <option
                    value="{{ $numero }}"
                    @selected((int) old('mes', $lote->mes ?? now()->month) === $numero)
                >
                    {{ $nombre }}
                </option>
            @endforeach
        </select>
        @error('mes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="gestion" class="form-label">
            Gestión <span class="text-danger">*</span>
        </label>
        <input
            type="number"
            name="gestion"
            id="gestion"
            min="2000"
            max="{{ now()->year + 1 }}"
            value="{{ old('gestion', $lote->gestion ?? now()->year) }}"
            class="form-control @error('gestion') is-invalid @enderror"
            required
        >
        @error('gestion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="fecha_recepcion" class="form-label">
            Fecha de recepción
        </label>
        <input
            type="date"
            name="fecha_recepcion"
            id="fecha_recepcion"
            value="{{ old(
                'fecha_recepcion',
                isset($lote) && $lote->fecha_recepcion
                    ? $lote->fecha_recepcion->format('Y-m-d')
                    : now()->format('Y-m-d')
            ) }}"
            class="form-control @error('fecha_recepcion') is-invalid @enderror"
        >
        @error('fecha_recepcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea
            name="observaciones"
            id="observaciones"
            rows="4"
            maxlength="2000"
            class="form-control @error('observaciones') is-invalid @enderror"
            placeholder="Ingrese una observación general del lote, si corresponde."
        >{{ old('observaciones', $lote->observaciones ?? '') }}</textarea>
        @error('observaciones')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@error('lote')
    <div class="alert alert-danger d-flex align-items-center mt-3 mb-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>{{ $message }}</div>
    </div>
@enderror

<div class="d-flex justify-content-end gap-2 mt-4">
    <a
        href="{{ $esEdicion
            ? route('procesamiento-mensual.lotes.show', $lote)
            : route('procesamiento-mensual.lotes.index') }}"
        class="btn btn-secondary"
    >
        <i class="bi bi-x-circle me-1"></i>
        Cancelar
    </a>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-floppy me-1"></i>
        {{ $esEdicion ? 'Actualizar lote' : 'Guardar lote' }}
    </button>
</div>