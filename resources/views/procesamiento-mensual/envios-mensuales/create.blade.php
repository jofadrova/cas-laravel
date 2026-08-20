<x-app-layout>
    <x-slot name="header">Nuevo lote de envío</x-slot>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-1"><i class="bi bi-send-plus text-success me-2"></i>Crear lote de envío mensual</h5>
                <small class="text-muted">Este lote inicia el ciclo mensual. Su recepción y procesamiento se registrarán después del envío.</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('procesamiento-mensual.envios-mensuales.store') }}" novalidate>
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label for="mes" class="form-label fw-semibold">Mes <span class="text-danger">*</span></label>
                            <select name="mes" id="mes" class="form-select @error('mes') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach($meses as $numero => $nombre)
                                    <option value="{{ $numero }}" @selected((int) old('mes', now()->month) === $numero)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            @error('mes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="gestion" class="form-label fw-semibold">Gestión <span class="text-danger">*</span></label>
                            <input type="number" name="gestion" id="gestion" min="2000" max="{{ now()->year + 1 }}" value="{{ old('gestion', now()->year) }}" class="form-control @error('gestion') is-invalid @enderror" required>
                            @error('gestion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Destinatario</label>
                            <input type="text" class="form-control" value="Ministerio de Defensa (MINDEF)" disabled>
                            <div class="form-text">En esta etapa el formato corresponde exclusivamente a MINDEF.</div>
                        </div>
                        <div class="col-12">
                            <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="4" maxlength="2000" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones') }}</textarea>
                            @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('procesamiento-mensual.envios-mensuales.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Crear lote</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
