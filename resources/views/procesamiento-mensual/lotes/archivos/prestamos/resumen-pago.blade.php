<x-app-layout>
    <x-slot name="header">
        Resumen del pago mensual · {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-success text-white py-4">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div
                        class="d-flex align-items-center justify-content-center bg-white text-success rounded-circle"
                        style="width: 58px; height: 58px;"
                    >
                        <i class="bi bi-check-lg fs-2"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Pago mensual consolidado correctamente</h3>
                        <div class="opacity-75">
                            {{ $lote->periodo }} · El lote quedó bloqueado para
                            evitar modificaciones o pagos duplicados.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6 col-xl">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Pagos registrados</div>
                            <div class="fs-3 fw-bold text-success">
                                {{ number_format((int) $procesamiento->cantidad_pagos) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Monto consolidado</div>
                            <div class="fs-4 fw-bold">
                                {{ number_format((float) $procesamiento->monto_total, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Cuotas normales</div>
                            <div class="fs-3 fw-bold">
                                {{ number_format($resumenPago['pagos_normales']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Pagos por garantes</div>
                            <div class="fs-3 fw-bold text-primary">
                                {{ number_format($resumenPago['pagos_garantes']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Préstamos cancelados</div>
                            <div class="fs-3 fw-bold text-success">
                                {{ number_format($resumenPago['solicitudes_finalizadas']) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-muted small mt-3">
                    <i class="bi bi-clock-history me-1"></i>
                    Procesado el
                    {{ \Carbon\Carbon::parse($procesamiento->fecha_procesamiento)->format('d/m/Y H:i:s') }}.
                    Se actualizaron
                    <strong>{{ number_format($resumenPago['solicitudes_actualizadas']) }}</strong>
                    solicitudes.
                </div>
            </div>
        </div>

        @if($resumenPago['operaciones_ignoradas'] > 0 || $resumenPago['garantes_pendientes'] > 0)
            <div class="alert alert-warning border-warning shadow-sm mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div class="w-100">
                        <div class="fw-bold mb-2">Operaciones no consolidadas</div>

                        @if($inconsistencias->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($inconsistencias as $inconsistencia)
                                    <span class="badge bg-white text-dark border">
                                        {{ str_replace('_', ' ', $inconsistencia->estado) }}:
                                        {{ number_format((int) $inconsistencia->total) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($resumenPago['garantes_pendientes'] > 0)
                            <div class="small mb-1">
                                Descuentos a garantes pendientes de completar:
                                <strong>{{ number_format($resumenPago['garantes_pendientes']) }}</strong>.
                            </div>
                        @endif

                        <div class="small">
                            Estas operaciones fueron ignoradas y no modificaron
                            pagos, cuotas ni saldos. Deberán resolverse
                            individualmente desde
                            <strong>Préstamos → Realizar pago</strong>.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-1">
                    <i class="bi bi-receipt-cutoff text-success me-2"></i>
                    Pagos generados
                </h5>
                <div class="text-muted small">
                    Registros creados en pagos y pagos_cuotas.
                </div>
            </div>

            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen', $lote) }}">
                    @if(request('id_solicitud_pago'))
                        <input type="hidden" name="id_solicitud_pago" value="{{ request('id_solicitud_pago') }}">
                    @endif
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="papeleta_pago" class="form-label fw-semibold">Papeleta</label>
                            <input type="text" class="form-control" id="papeleta_pago" name="papeleta_pago"
                                value="{{ request('papeleta_pago') }}" placeholder="Buscar por número de papeleta">
                        </div>
                        <div class="col-md-5">
                            <label for="nombre_pago" class="form-label fw-semibold">Nombre del socio</label>
                            <input type="text" class="form-control" id="nombre_pago" name="nombre_pago"
                                value="{{ request('nombre_pago') }}" placeholder="Buscar por apellido o nombre">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                                <a href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen', [
                                        'lote' => $lote,
                                        'id_solicitud_pago' => request('id_solicitud_pago'),
                                    ]) }}"
                                    class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pago</th>
                            <th>Solicitud</th>
                            <th>Papeleta</th>
                            <th>Nombre del socio</th>
                            <th>Tipo</th>
                            <th>Cuota</th>
                            <th>Concepto</th>
                            <th>Anexo</th>
                            <th class="text-end">Monto</th>
                            <th class="text-end">Saldo</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                            <tr>
                                <td class="fw-semibold">#{{ $pago->pago_id }}</td>
                                <td>{{ $pago->id_solicitud }}</td>
                                <td>{{ $pago->papeleta ?: 'Sin papeleta' }}</td>
                                <td>
                                    {{ trim(implode(' ', array_filter([
                                        $pago->paterno,
                                        $pago->materno,
                                        $pago->nombres,
                                    ]))) ?: 'Sin nombre' }}
                                </td>
                                <td>{{ $pago->descripcion_tasa ?: 'Sin descripción' }}</td>
                                <td>
                                    N.º {{ $pago->nro_cuota }}
                                    <div class="text-muted small">
                                        ID {{ $pago->id_cuota_solicitud }}
                                    </div>
                                </td>
                                <td>
                                    @if($pago->concepto === \App\Models\LotePrestamoConciliacion::CONCEPTO_GARANTE)
                                        <span class="badge bg-primary">
                                            Descuento a garantes
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Cuota normal
                                        </span>
                                    @endif
                                </td>
                                <td class="small">{{ $pago->anexo }}</td>
                                <td class="text-end fw-semibold">
                                    {{ $pago->tipo_moneda === 'U' ? '$us' : 'Bs' }}
                                    {{ number_format((float) $pago->monto, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $pago->saldo_cuota, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">
                                        {{ $pago->estado_cuota }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    No existen pagos que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="8" class="text-end">Total consolidado</td>
                            <td class="text-end">
                                {{ number_format((float) $procesamiento->monto_total, 2, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($pagos->hasPages())
                <div class="card-footer bg-white">
                    {{ $pagos->links() }}
                </div>
            @endif
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-1">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i>
                    Actualización de solicitudes
                </h5>
                <div class="text-muted small">
                    Cambios aplicados en última cuota, saldo actual y estado.
                </div>
            </div>

            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen', $lote) }}">
                    @if(request('papeleta_pago'))
                        <input type="hidden" name="papeleta_pago" value="{{ request('papeleta_pago') }}">
                    @endif
                    @if(request('nombre_pago'))
                        <input type="hidden" name="nombre_pago" value="{{ request('nombre_pago') }}">
                    @endif
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="id_solicitud_pago" class="form-label fw-semibold">ID de solicitud</label>
                            <input type="number" min="1" class="form-control" id="id_solicitud_pago"
                                name="id_solicitud_pago" value="{{ request('id_solicitud_pago') }}"
                                placeholder="Ingrese el ID exacto de la solicitud">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                                <a href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen', [
                                        'lote' => $lote,
                                        'papeleta_pago' => request('papeleta_pago'),
                                        'nombre_pago' => request('nombre_pago'),
                                    ]) }}" class="btn btn-outline-secondary" title="Limpiar búsqueda de solicitudes">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Solicitud</th>
                            <th class="text-center">Última cuota anterior</th>
                            <th class="text-center">Última cuota nueva</th>
                            <th class="text-end">Saldo anterior</th>
                            <th class="text-end">Saldo nuevo</th>
                            <th class="text-center">Estado anterior</th>
                            <th class="text-center">Estado nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $solicitud)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $solicitud->id_solicitud }}
                                </td>
                                <td class="text-center">
                                    {{ $solicitud->ultima_cuota_anterior }}
                                </td>
                                <td class="text-center fw-semibold">
                                    {{ $solicitud->ultima_cuota_nueva }}
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $solicitud->saldo_actual_anterior, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ number_format((float) $solicitud->saldo_actual_nuevo, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        {{ $solicitud->estado_anterior }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{
                                        $solicitud->estado_nuevo === 'PA'
                                            ? 'bg-success'
                                            : 'bg-primary'
                                    }}">
                                        {{ $solicitud->estado_nuevo }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No existe una solicitud con el ID indicado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($solicitudes->hasPages())
                <div class="card-footer bg-white">
                    {{ $solicitudes->links() }}
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-end">
            <a
                href="{{ route('procesamiento-mensual.lotes.show', $lote) }}"
                class="btn btn-success px-5"
            >
                <i class="bi bi-check-circle-fill me-2"></i>
                Aceptar
            </a>
        </div>
    </div>
</x-app-layout>
