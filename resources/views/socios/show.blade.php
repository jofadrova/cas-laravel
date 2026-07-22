<x-app-layout>
    <x-slot name="header">Información del Socio</x-slot>
    @fragment('informacion-socio')
    <div class="card shadow-sm mb-4">
        <div class="card-header scas-header-light">
            <i class="fas fa-eye me-2"></i>
            Información del Socio
        </div>
        <div class="card-body">
            <div class="row">         
                <div class="col-md-3 text-center">
                    @if($socio->foto)
                        <img src="{{ asset('storage/socios/'.$socio->foto) }}"
                            class="img-thumbnail"
                            style="max-height:220px;">
                    @else
                        <img src="{{ asset('images/user-default.png') }}"
                            class="img-thumbnail"
                            style="max-height:220px;">
                    @endif
                </div>
                <div class="col-md-9">
                    <h3 class="fw-bold">
                        {{ $socio->paterno }}
                        {{ $socio->materno }}
                        {{ $socio->nombres }}
                    </h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Nro Papeleta:</strong>
                            {{ $socio->institucion?->papeleta }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Documento:</strong>
                            {{ $socio->nro_doc }}
                            {{ $socio->expedido }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Fecha Nacimiento:</strong>
                            {{ \Carbon\Carbon::parse($socio->fecha_nac)->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Edad:</strong>
                            {{ \Carbon\Carbon::parse($socio->fecha_nac)->age }}
                            años
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="card shadow-sm">
    <div class="card-body">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#resumen">
                    Resumen
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documentacion">
                    Documentación
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#prestamos">
                    Préstamos
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#garantes">
                    Garantes
                </button>
            </li>
        </ul>
        <div class="tab-content mt-4">
            <div class="tab-pane fade show active" id="resumen">
                <div class="alert alert-info">
                    <i class="fas fa-circle-info me-2"></i>
                    Información financiera pendiente de migración.
                </div>
                <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-header">
                            Total CAO
                        </div>
                        <div class="card-body">
                            0.00
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-header">
                            Total CAV
                        </div>
                        <div class="card-body">
                            0.00
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-header">
                            Total CPI
                        </div>
                        <div class="card-body">
                            0.00
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-header">
                            Certificados
                        </div>
                        <div class="card-body">
                            0.00
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="documentacion">
        <div class="mt-3">
            <div class="mb-2">
                {!! $socio->residencia?->formularioSolicitud == 'FA'
                    ? '<span class="text-success">✔</span>'
                    : '<span class="text-danger">✘</span>' !!}

                Formulario de Solicitud
            </div>
            <div class="mb-2">
                {!! $socio->residencia?->afiliacionAfcoop == 'SI'
                    ? '<span class="text-success">✔</span>'
                    : '<span class="text-danger">✘</span>' !!}
                Formulario AFCOOP
            </div>
            <div class="mb-2">
                {!! $socio->residencia?->fotocopiaCarnet == 'SI'
                    ? '<span class="text-success">✔</span>'
                    : '<span class="text-danger">✘</span>' !!}
                Fotocopia de Carnet
            </div>
            <div class="mt-3">
                <strong>Resolución AFCOOP:</strong>
                {{ $socio->residencia?->resolucion }}
            </div>
        </div>
    </div>

<div class="tab-pane fade" id="prestamos">
    <div class="row g-3 mt-1 mb-4">
        <div class="col-md-3">
            <div class="card border-primary h-100 text-center">
                <div class="card-body">
                    <div class="text-muted small">Total préstamos</div>
                    <div class="fs-3 fw-bold text-primary">{{ $prestamosPropios->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success h-100 text-center">
                <div class="card-body">
                    <div class="text-muted small">Activos</div>
                    <div class="fs-3 fw-bold text-success">{{ $prestamosPropios->where('estado', 'AC')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary h-100 text-center">
                <div class="card-body">
                    <div class="text-muted small">Concluidos</div>
                    <div class="fs-3 fw-bold text-secondary">{{ $prestamosPropios->whereIn('estado', ['PA', 'CE'])->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning h-100 text-center">
                <div class="card-body">
                    <div class="text-muted small">Saldo vigente</div>
                    <div class="fw-bold text-danger">
                        Bs {{ number_format(
                            $prestamosPropios
                                ->where('estado', 'AC')
                                ->reject(fn ($prestamo) => $prestamo->tipo?->tipo_moneda === 'SU')
                                ->sum('saldo_actual'),
                            2
                        ) }}
                    </div>
                    <div class="fw-bold text-danger">
                        $us {{ number_format(
                            $prestamosPropios
                                ->where('estado', 'AC')
                                ->filter(fn ($prestamo) => $prestamo->tipo?->tipo_moneda === 'SU')
                                ->sum('saldo_actual'),
                            2
                        ) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">Resumen por tipo de préstamo</div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tipo</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Activos</th>
                        <th class="text-center">Cancelados</th>
                        <th class="text-center">Refinanciados</th>
                        <th class="text-end">Monto histórico</th>
                        <th class="text-end">Saldo actual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumenPorTipo as $resumen)
                        <tr>
                            <td>{{ $resumen['tipo'] }}</td>
                            <td class="text-center">{{ $resumen['total'] }}</td>
                            <td class="text-center">{{ $resumen['activos'] }}</td>
                            <td class="text-center">{{ $resumen['cancelados'] }}</td>
                            <td class="text-center">{{ $resumen['refinanciados'] }}</td>
                            <td class="text-end">
                                {{ $resumen['simbolo_moneda'] }} {{ number_format($resumen['monto'], 2) }}
                            </td>
                            <td class="text-end">
                                {{ $resumen['simbolo_moneda'] }} {{ number_format($resumen['saldo'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Sin préstamos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white fw-bold">Historial completo de préstamos</div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Solicitud</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="text-end">Monto / saldo</th>
                        <th class="text-center">Cuotas</th>
                        <th>Estado</th>
                        <th>Garantes actuales e históricos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestamosPropios as $prestamo)
                        @php($simbolo = $prestamo->tipo?->tipo_moneda === 'SU' ? '$us' : 'Bs')
                        <tr>
                            <td class="fw-bold">{{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $prestamo->fecha_deposito ? \Carbon\Carbon::parse($prestamo->fecha_deposito)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $prestamo->tipo?->descripcion_tasa ?? 'Tipo no registrado' }}</td>
                            <td class="text-end">
                                <div>{{ $simbolo }} {{ number_format($prestamo->monto, 2) }}</div>
                                <small class="text-danger">Saldo: {{ number_format($prestamo->saldo_actual, 2) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="text-success">{{ $prestamo->cuotas_pagadas_count }} pagadas</span><br>
                                <span class="text-warning">{{ $prestamo->cuotas_pendientes_count }} pendientes</span>
                            </td>
                            <td>
                                <span class="badge {{ $prestamo->estado === 'AC' ? 'bg-success' : ($prestamo->estado === 'CE' ? 'bg-dark' : 'bg-secondary') }}">
                                    {{ $prestamo->estado_texto }}
                                </span>
                            </td>
                            <td>
                                <div><strong>G1:</strong> {{ trim(($prestamo->garante1?->paterno ?? '').' '.($prestamo->garante1?->nombres ?? '')) ?: 'Sin garante' }}</div>
                                <div><strong>G2:</strong> {{ trim(($prestamo->garante2?->paterno ?? '').' '.($prestamo->garante2?->nombres ?? '')) ?: 'Sin garante' }}</div>
                                @if($prestamo->historialGarantes->isNotEmpty())
                                    <details class="mt-1">
                                        <summary class="text-primary">{{ $prestamo->historialGarantes->count() }} cambio(s) de garantes</summary>
                                        @foreach($prestamo->historialGarantes as $cambio)
                                            <div class="small border-top mt-1 pt-1">
                                                {{ \Carbon\Carbon::parse($cambio->fecha)->format('d/m/Y') }}:
                                                {{ trim(($cambio->garante1Old?->paterno ?? '').' '.($cambio->garante1Old?->nombres ?? '')) ?: 'Sin G1' }}
                                                /
                                                {{ trim(($cambio->garante2Old?->paterno ?? '').' '.($cambio->garante2Old?->nombres ?? '')) ?: 'Sin G2' }}
                                                <i class="bi bi-arrow-right"></i>
                                                {{ trim(($cambio->garante1New?->paterno ?? '').' '.($cambio->garante1New?->nombres ?? '')) ?: 'Sin G1' }}
                                                /
                                                {{ trim(($cambio->garante2New?->paterno ?? '').' '.($cambio->garante2New?->nombres ?? '')) ?: 'Sin G2' }}
                                            </div>
                                        @endforeach
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin historial de préstamos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="tab-pane fade" id="garantes">
    <div class="row g-3 mt-1 mb-4">
        <div class="col-md-4">
            <div class="card border-info h-100 text-center"><div class="card-body">
                <div class="text-muted">Préstamos garantizados</div>
                <div class="fs-3 fw-bold text-info">{{ $prestamosComoGarante->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-success h-100 text-center"><div class="card-body">
                <div class="text-muted">Garantías vigentes</div>
                <div class="fs-3 fw-bold text-success">{{ $prestamosComoGarante->where('estado', 'AC')->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary h-100 text-center"><div class="card-body">
                <div class="text-muted">Concluidas o históricas</div>
                <div class="fs-3 fw-bold text-secondary">{{ $prestamosComoGarante->where('estado', '!=', 'AC')->count() }}</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white fw-bold">Personas y préstamos garantizados</div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered align-middle mb-0">
                <thead class="table-light"><tr>
                    <th>Solicitud</th><th>Titular</th><th>Papeleta</th><th>Tipo</th>
                    <th class="text-end">Monto / saldo</th><th>Participación</th><th>Estado</th>
                </tr></thead>
                <tbody>
                    @forelse($prestamosComoGarante as $prestamo)
                        @php($simbolo = $prestamo->tipo?->tipo_moneda === 'SU' ? '$us' : 'Bs')
                        <tr>
                            <td class="fw-bold">{{ str_pad($prestamo->nro_solicitud, 8, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ trim(($prestamo->socio?->paterno ?? '').' '.($prestamo->socio?->materno ?? '').' '.($prestamo->socio?->nombres ?? '')) ?: 'No registrado' }}</td>
                            <td>{{ $prestamo->socio?->institucion?->papeleta ?? '-' }}</td>
                            <td>{{ $prestamo->tipo?->descripcion_tasa ?? 'Tipo no registrado' }}</td>
                            <td class="text-end">
                                <div>{{ $simbolo }} {{ number_format($prestamo->monto, 2) }}</div>
                                <small class="text-danger">Saldo: {{ number_format($prestamo->saldo_actual, 2) }}</small>
                            </td>
                            <td>{{ $prestamo->roles_garantia ?: 'Garante histórico' }}</td>
                            <td><span class="badge {{ $prestamo->estado === 'AC' ? 'bg-success' : 'bg-secondary' }}">{{ $prestamo->estado_texto }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No figura como garante.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</div>
</div>
</div>
<div class="mt-4 text-end">

    <a href="{{ route('socios.index') }}"
       class="btn btn-secondary">

        <i class="fas fa-arrow-left me-1"></i>

        Aceptar

    </a>

</div>
    @endfragment
</x-app-layout>
