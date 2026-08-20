@php
    $esEdicion = isset($lote);
@endphp

<div class="row g-3">
    @if(! $esEdicion)
        <div class="col-md-8">
            <label for="envio_mensual_id" class="form-label">
                Lote enviado que se está recibiendo <span class="text-danger">*</span>
            </label>
            <select name="envio_mensual_id" id="envio_mensual_id" class="form-select @error('envio_mensual_id') is-invalid @enderror" required>
                <option value="">Seleccione un lote enviado...</option>
                @foreach($enviosDisponibles as $envio)
                    <option value="{{ $envio->id }}" @selected((string) old('envio_mensual_id', request('envio_mensual_id')) === (string) $envio->id)>
                        {{ $envio->codigo }} — {{ $envio->periodo }} — enviado {{ $envio->fecha_envio?->format('d/m/Y') ?? 'sin fecha' }}
                    </option>
                @endforeach
            </select>
            @error('envio_mensual_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($enviosDisponibles->isEmpty())
                <div class="form-text text-warning">No existen lotes marcados como enviados pendientes de recepción.</div>
            @endif
        </div>
    @elseif($lote->envioMensual)
        <div class="col-md-4">
            <div class="text-muted small">Lote de origen</div>
            <div class="fw-semibold">{{ $lote->envioMensual->codigo }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small">Periodo</div>
            <div class="fw-semibold">{{ $lote->envioMensual->periodo }}</div>
        </div>
    @else
        <div class="col-md-3">
            <label for="mes" class="form-label">Mes <span class="text-danger">*</span></label>
            <select name="mes" id="mes" class="form-select @error('mes') is-invalid @enderror" required>
                @foreach($meses as $numero => $nombre)
                    <option value="{{ $numero }}" @selected((int) old('mes', $lote->mes) === $numero)>{{ $nombre }}</option>
                @endforeach
            </select>
            @error('mes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label for="gestion" class="form-label">Gestión <span class="text-danger">*</span></label>
            <input type="number" name="gestion" id="gestion" value="{{ old('gestion', $lote->gestion) }}" class="form-control @error('gestion') is-invalid @enderror" required>
            @error('gestion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif

    <div class="col-md-3">
        <label for="fecha_recepcion" class="form-label">
            Fecha de recepción <span class="text-danger">*</span>
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
            required
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
