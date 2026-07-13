<x-app-layout>
    <x-slot name="header">Cambio de Garantes</x-slot>
    <div class="container-fluid">
        <form method="POST" id="frmGarantes" action="{{ route('prestamos.garantes.update',$prestamo) }}">
            @csrf
            @method('PATCH')
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Datos generales del préstamo
                </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Solicitud</label>
                        <div>{{ $prestamo->nro_solicitud }}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Papeleta</label>
                        <div>{{ $prestamo->socio->institucion->papeleta }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Asociado</label>
                        <div>{{ implode(' ', array_filter([
                                    optional($prestamo->socio)->paterno,
                                    optional($prestamo->socio)->materno,
                                    optional($prestamo->socio)->nombres,
                                ])) }}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tipo</label>
                        <div>{{ $prestamo->tipo->descripcion_tasa }}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Estado</label>
                        @if($prestamo->estado=='AC')
                            <span class="badge bg-success">ACTIVO</span>
                        @elseif($prestamo->estado=='PE')
                            <span class="badge bg-warning text-dark">PENDIENTE</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Monto</label>
                        @if ($prestamo->tipo->tipo_moneda == 'SU')
                            <div>$us {{ number_format($prestamo->monto,2,',','.') }}</div>
                        @else
                            <div>Bs. {{ number_format($prestamo->monto,2,',','.') }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Saldo</label>
                        @if ($prestamo->tipo->tipo_moneda == 'SU')
                            <div>$us {{ number_format($prestamo->saldo_actual,2,',','.') }}</div>
                        @else
                            <div>Bs. {{ number_format($prestamo->saldo_actual,2,',','.') }}</div>
                        @endif                      
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Plazo</label>
                        <div>{{ $prestamo->periodo }} meses</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha depósito</label>
                        <div>{{ \Carbon\Carbon::parse($prestamo->fecha_deposito)->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>           
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-shield-check me-2"></i>
                Garantes actualmente registrados
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Garante actual 1</label>
                        <div class="border rounded p-3 bg-light position-relative">
                            <div class="form-check position-absolute top-0 end-0 mt-3 me-3">
                                <input class="form-check-input" type="checkbox" id="chkGarante1">
                                <label class="form-check-label" for="chkGarante1"> Cambiar</label>
                            </div>
                            <div class="small text-muted">Nombre</div>
                            <div class="fw-semibold mb-2">
                                {{ optional($prestamo->garante1)->paterno }}
                                {{ optional($prestamo->garante1)->materno }}
                                {{ optional($prestamo->garante1)->nombres }}
                            </div>
                            <div class="small text-muted">Papeleta</div>
                            <div class="fw-semibold">
                                {{ optional($prestamo->garante1->institucion)->papeleta }}
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Garante actual 2</label>
                        <div class="border rounded p-3 bg-light position-relative">
                            <div class="form-check position-absolute top-0 end-0 mt-3 me-3">
                                <input class="form-check-input"  type="checkbox" id="chkGarante2"> 
                                <label class="form-check-label" for="chkGarante2"> Cambiar </label>
                            </div>
                            <div class="small text-muted">Nombre</div>
                            <div class="fw-semibold mb-2">
                                {{ optional($prestamo->garante2)->paterno }}
                                {{ optional($prestamo->garante2)->materno }}
                                {{ optional($prestamo->garante2)->nombres }}
                            </div>
                            <div class="small text-muted">Papeleta</div>
                            <div class="fw-semibold">
                                {{ optional($prestamo->garante2->institucion)->papeleta }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm d-none" id="cardNuevosGarantes">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-people-fill me-2"></i>
                Nuevos garantes
            </div>            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 d-none" id="bloqueGarante1">
                        <x-scas.papeleta-search name="id_garante1" label="Nuevo garante 1" :value="old('id_garante1')" /> 
                    </div>
                    <div class="col-md-6 d-none" id="bloqueGarante2">
                        <x-scas.papeleta-search name="id_garante2" label="Nuevo garante 2" :value="old('id_garante2')" />
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">Justificación del cambio</label>
                        <textarea style="resize:none" id="observaciones" name="observaciones" rows="3" maxlength="500" class="form-control"
                            placeholder="Describa el motivo del cambio de garantes...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>  
        </div> 
        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
            <button class="btn btn-success" id="btnGuardarGarantes">
                <i class="bi bi-check-circle me-1"></i>
                Actualizar garantes
            </button>
        </div>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-clock-history me-2"></i>
                Historial de cambios
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Tipo</th>
                                <th>Observaciones</th>
                                <th class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestamo->historialGarantes as $historial)
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($historial->fecha)->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $historial->usuario->name }}
                                    </td>
                                    <td>
                                        {{ $historial->tipo_cambio }}
                                    </td>
                                    <td>
                                        {{ Str::limit($historial->observaciones,60) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('prestamos.garantes.pdf', $historial) }}" target="_blank" class="btn btn-outline-danger btn-sm"
                                            title="Ver documento">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No existen cambios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="garante1Original" value="{{ $prestamo->id_garante1 }}">
    <input type="hidden" id="garante2Original" value="{{ $prestamo->id_garante2 }}">
    <input type="hidden" id="idSocio" value="{{ $prestamo->ide_per }}">
</div>
<div class="modal fade" id="modalValidacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Validación
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p id="mensajeValidacion" class="mb-0"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Aceptar
                </button>
            </div>

        </div>
    </div>
</div>
</x-app-layout>
