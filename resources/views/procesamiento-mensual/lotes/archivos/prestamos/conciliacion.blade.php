<x-app-layout>
    <x-slot name="header">
        Conciliación de préstamos · {{ $lote->periodo }}
    </x-slot>

    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $errors->has('archivo') || $errors->has('hash_preview')
                        ? 'No fue posible incorporar la planilla adicional de MinDef'
                        : 'No fue posible cargar el archivo de garantes' }}
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-1">Comparación de préstamos</h4>
                <div class="text-muted">
                    {{ $lote->periodo }}
                    <span class="mx-1">·</span>
                    El lote permanece en
                    <span class="badge {{ $lote->clase_estado }}">
                        {{ $lote->estado }}
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form
                    id="formRecompararPrestamos"
                    method="POST"
                    action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.comparar', $lote) }}"
                >
                    @csrf
                    <button
                        type="submit"
                        id="btnRecompararPrestamos"
                        class="btn btn-success"
                        @disabled($procesamientoPago)
                        data-bloqueado="{{ $procesamientoPago ? '1' : '0' }}"
                        title="{{ $procesamientoPago
                            ? 'El lote ya fue procesado y no puede volver a compararse'
                            : 'Ejecutar nuevamente la comparación' }}"
                    >
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Volver a comparar
                    </button>
                </form>

                @if($procesamientoPago)
                    <button
                        type="button"
                        class="btn btn-secondary"
                        disabled
                        title="El pago mensual de este lote ya fue consolidado"
                    >
                        <i class="bi bi-lock-fill me-1"></i>
                        Pago mensual realizado
                    </button>
                    <a
                        href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.resumen', $lote) }}"
                        class="btn btn-outline-success"
                    >
                        <i class="bi bi-receipt-cutoff me-1"></i>
                        Consultar resumen
                    </a>
                @else
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfirmarPagoMensual"
                        @disabled(! $puedeRealizarPago)
                        title="{{ $puedeRealizarPago
                            ? 'Consolidar las operaciones válidas e ignorar las inconsistencias'
                            : 'Primero debe completarse la comparación del lote' }}"
                    >
                        <i class="bi bi-cash-coin me-1"></i>
                        Realizar el pago mensual
                    </button>
                @endif

                <button
                    type="button"
                    class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalOtrosArchivosPrestamos"
                    @disabled($procesamientoPago)
                    title="{{ $procesamientoPago
                        ? 'El pago mensual ya fue consolidado y la conciliación no puede modificarse'
                        : 'Previsualizar e incorporar la planilla adicional de MinDef' }}"
                >
                    <i class="bi bi-folder2-open me-1"></i>
                    OTROS ARCHIVOS
                </button>

                @if($registrosGarantes->isNotEmpty())
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalSeguimientoGarantes"
                    >
                        <i class="bi bi-hourglass-split me-1"></i>
                        Seguimiento de garantes
                        <span class="badge bg-primary ms-1">
                            {{ number_format($registrosGarantes->count()) }}
                        </span>
                    </button>
                @endif

                <a
                    href="{{ route('procesamiento-mensual.lotes.archivos.index', $lote) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver a archivos
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-people-fill text-success me-2"></i>
                            Descuentos a garantes
                        </h5>
                        <div class="text-muted small">
                            Archivo complementario de Cartera de Crédito.
                            Solo se aplica a préstamos REGULARES.
                        </div>
                    </div>
                    @if($archivosGarantes->isNotEmpty() && $puedeCargarGarantes)
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalLimpiarGarantes"
                        >
                            <i class="bi bi-trash3-fill me-1"></i>
                            Limpiar garantes
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body">
                @if($puedeCargarGarantes)
                    <form
                        id="formCargaGarantes"
                        method="POST"
                        action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.garantes.store', $lote) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-9">
                                <label
                                    for="archivos_garantes"
                                    class="form-label fw-semibold"
                                >
                                    Archivo Excel de descuentos a garantes
                                </label>
                                <input
                                    type="file"
                                    id="archivos_garantes"
                                    name="archivos_garantes[]"
                                    class="form-control @error('archivos_garantes') is-invalid @enderror"
                                    accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                    multiple
                                    required
                                >
                                <div class="form-text">
                                    El periodo se toma del lote
                                    <strong>{{ $lote->periodo }}</strong>.
                                    Los códigos de titular y garante se normalizan
                                    eliminando ceros iniciales. Al cargar un nuevo
                                    archivo, reemplazará la importación anterior de
                                    garantes y la comparación se ejecutará
                                    automáticamente.
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <button
                                    type="submit"
                                    id="btnCargarGarantes"
                                    class="btn btn-success w-100"
                                >
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i>
                                    Subir archivo de garantes
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-lock-fill me-2"></i>
                        La carga de garantes está bloqueada porque el lote se
                        encuentra <strong>{{ $lote->estado }}</strong>.
                    </div>
                @endif
            </div>

            @if($archivosGarantes->isNotEmpty())
                <div class="table-responsive border-top">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Archivo</th>
                                <th class="text-center">Filas</th>
                                <th class="text-end">Monto Bs</th>
                                <th class="text-center">Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivosGarantes as $archivo)
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                        {{ $archivo->nombre_original }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($archivo->filas_importadas) }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format((float) $archivo->total_monto_descuento, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            {{ $archivo->estado }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $archivo->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($resumenGlobal['total'] > 0)
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
                <div>
                    <h5 class="mb-1">Resumen global de la conciliación</h5>
                    <div class="text-muted small">
                        Incluye cuotas de la base de datos, descuentos del Excel
                        general y descuentos a garantes.
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Operaciones comparadas</div>
                            <div class="fs-4 fw-bold">
                                {{ number_format($resumenGlobal['total']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Monto informado (Excel)</div>
                            <div class="fs-5 fw-bold">
                                Bs {{ number_format($resumenGlobal['monto_bs'], 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Pendientes</div>
                            <div class="fs-4 fw-bold text-warning">
                                {{ number_format($resumenGlobal['pendientes']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Listos para aplicar</div>
                            <div class="fs-4 fw-bold text-success">
                                {{ number_format($resumenGlobal['listos']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="text-muted small">Observados</div>
                            <div class="fs-4 fw-bold text-danger">
                                {{ number_format($resumenGlobal['observados']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="alert {{ $integridadCompleta ? 'alert-success' : 'alert-danger' }}">
            <div class="d-flex align-items-center gap-2">
                <i class="bi {{ $integridadCompleta ? 'bi-shield-check' : 'bi-exclamation-octagon-fill' }} fs-4"></i>
                <div>
                    <div class="fw-semibold">
                        Control de integridad:
                        {{ number_format($totalImportados) }} importados =
                        {{ number_format($totalRegistrosAtendidos) }} atendidos
                    </div>
                    <div class="small">
                        {{ $integridadCompleta
                            ? number_format($totalOperacionesClasificadas)
                                . ' operaciones de préstamo fueron clasificadas individualmente.'
                            : 'La conciliación está incompleta y debe ejecutarse nuevamente.' }}
                    </div>
                </div>
            </div>
        </div>

        @php
            $tarjetas = [
                'COINCIDE' => ['COINCIDE', 'bg-success', 'bi-check-circle-fill'],
                'FALTA' => ['FALTA', 'bg-danger', 'bi-dash-circle-fill'],
                'DEMASIA' => ['DEMASÍA', 'bg-warning text-dark', 'bi-plus-circle-fill'],
                'SOCIO_NO_ENCONTRADO' => ['SOCIO NO ENCONTRADO', 'bg-dark', 'bi-person-x-fill'],
                'SIN_CUOTA' => ['SIN CUOTA', 'bg-secondary', 'bi-calendar-x-fill'],
                'TIPO_NO_CLASIFICADO' => ['TIPO NO CLASIFICADO', 'bg-info text-dark', 'bi-question-circle-fill'],
            ];
        @endphp

        <div class="row g-3 mb-4">
            @foreach($tarjetas as $estado => [$etiqueta, $clase, $icono])
                <div class="col-sm-6 col-xl">
                    <a
                        href="{{ route(
                            'procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index',
                            array_filter([
                                'lote' => $lote,
                                'clasificacion' => $estado,
                                'papeleta' => $papeletaBuscada,
                                'nombre' => $nombreBuscado,
                            ])
                        ) }}"
                        class="text-decoration-none"
                    >
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <div class="text-muted small">
                                            {{ $etiqueta }}
                                        </div>
                                        <div class="fs-4 fw-bold text-dark">
                                            {{ number_format($resumen[$estado]) }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $clase }} align-self-start">
                                        <i class="bi {{ $icono }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6 col-xl-3">
                        <label for="clasificacion" class="form-label fw-semibold">
                            Clasificación
                        </label>
                        <select
                            id="clasificacion"
                            name="clasificacion"
                            class="form-select"
                        >
                            <option value="">Todas</option>
                            @foreach($clasificaciones as $estado)
                                <option
                                    value="{{ $estado }}"
                                    @selected($clasificacionSeleccionada === $estado)
                                >
                                    {{ $tarjetas[$estado][0] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label for="papeleta" class="form-label fw-semibold">
                            Papeleta
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-hash text-muted"></i>
                            </span>
                            <input
                                type="search"
                                id="papeleta"
                                name="papeleta"
                                class="form-control"
                                value="{{ $papeletaBuscada }}"
                                placeholder="Ej.: 21829"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <div class="col-md-8 col-xl-4">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre del socio
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input
                                type="search"
                                id="nombre"
                                name="nombre"
                                class="form-control"
                                value="{{ $nombreBuscado }}"
                                placeholder="Buscar por nombre o apellido"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                    @if(
                        $clasificacionSeleccionada !== ''
                        || $papeletaBuscada !== ''
                        || $nombreBuscado !== ''
                    )
                        <div class="col-auto">
                            <a
                                href="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.index', $lote) }}"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Origen</th>
                            <th>CODIGO_PERSONAL</th>
                            <th>Socio</th>
                            <th>Concepto</th>
                            <th class="text-end">Monto asignado</th>
                            <th class="text-end">Monto esperado</th>
                            <th class="text-end">Diferencia</th>
                            <th>Préstamo</th>
                            <th>Clasificación</th>
                            <th class="text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conciliaciones as $conciliacion)
                            @php
                                $detallePrincipal = $conciliacion->detalles->first();
                            @endphp
                            <tr>
                                <td class="small">
                                    <div>{{ $conciliacion->registro?->archivo?->nombre_original }}</div>
                                    <div class="text-muted">
                                        Fila {{ $conciliacion->registro?->fila_origen }}
                                    </div>
                                </td>
                                <td class="fw-semibold">
                                    {{ $conciliacion->registro?->codigo_personal_normalizado ?: '—' }}
                                </td>
                                <td>
                                    <div>{{ $conciliacion->registro?->nombres }}</div>
                                    <div class="text-muted small">
                                        CI {{ $conciliacion->registro?->carnet ?: '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{
                                        $conciliacion->concepto
                                            === \App\Models\LotePrestamoConciliacion::CONCEPTO_GARANTE
                                                ? 'bg-primary'
                                                : 'bg-secondary'
                                    }}">
                                        {{ $conciliacion->concepto_texto }}
                                    </span>

                                    @if($conciliacion->garanteRegistro)
                                        <div class="small mt-2">
                                            <div class="fw-semibold">
                                                Titular:
                                                {{ $conciliacion->garanteRegistro->nombre_titular }}
                                            </div>
                                            <div class="text-muted">
                                                Papeleta
                                                {{ $conciliacion->garanteRegistro->codigo_titular }}
                                                · {{ $conciliacion->garanteRegistro->tipo_garante }}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $conciliacion->monto_excel_asignado, 2, ',', '.') }}
                                    <div class="text-muted small">
                                        {{
                                            $conciliacion->concepto
                                                === \App\Models\LotePrestamoConciliacion::CONCEPTO_GARANTE
                                                    ? 'Comparado en Bs:'
                                                    : 'Ajustado:'
                                        }}
                                        {{ number_format((float) $conciliacion->monto_excel, 2, ',', '.') }}
                                    </div>
                                    @if($conciliacion->registro)
                                        <div class="text-muted small">
                                            Total Excel:
                                            {{ number_format((float) $conciliacion->registro->monto_descuento, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold
                                    {{ (float) $conciliacion->diferencia < -0.01
                                        ? 'text-danger'
                                        : ((float) $conciliacion->diferencia > 0.01
                                            ? 'text-warning'
                                            : 'text-success') }}"
                                >
                                    {{ number_format((float) $conciliacion->diferencia, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($detallePrincipal)
                                        <div class="fw-semibold">
                                            Tipo {{ $detallePrincipal->tipo_prestamo }}
                                        </div>
                                        <div class="text-muted small">
                                            Solicitud {{ $detallePrincipal->id_solicitud }}
                                            · Cuota N.º {{ $detallePrincipal->nro_cuota }}
                                        </div>
                                        @if($conciliacion->garanteRegistro)
                                            <div class="mt-1">
                                                <span class="badge {{ $conciliacion->garanteRegistro->clase_aplicacion }}">
                                                    {{ str_replace('_', ' ', $conciliacion->garanteRegistro->estado_aplicacion) }}
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        @if($conciliacion->garanteRegistro)
                                            <span class="badge bg-danger">
                                                {{ str_replace('_', ' ', $conciliacion->garanteRegistro->estado_aplicacion) }}
                                            </span>
                                            <div class="text-danger small mt-1">
                                                {{ $conciliacion->garanteRegistro->observacion_sistema }}
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $conciliacion->clase_badge }}">
                                        {{ $conciliacion->clasificacion_texto }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalleConciliacion"
                                        data-detalle-template="detalleConciliacion{{ $conciliacion->id }}"
                                        data-codigo-personal="{{ $conciliacion->registro?->codigo_personal_normalizado }}"
                                        title="Ver detalle"
                                        aria-label="Ver detalle de {{ $conciliacion->registro?->codigo_personal_normalizado }}"
                                    >
                                        <i class="bi bi-eye-fill me-1"></i>
                                        Detalle
                                    </button>

                                    <template id="detalleConciliacion{{ $conciliacion->id }}">
                                        <div class="alert alert-light border mb-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                                                <div>{{ $conciliacion->observacion }}</div>
                                            </div>
                                        </div>

                                        @if($conciliacion->garanteRegistro)
                                            <div class="alert alert-primary mb-3">
                                                <div class="fw-semibold mb-1">
                                                    <i class="bi bi-person-check-fill me-1"></i>
                                                    Aplicación al préstamo del titular
                                                </div>
                                                <div>
                                                    Titular:
                                                    {{ $conciliacion->garanteRegistro->nombre_titular }}
                                                    ({{ $conciliacion->garanteRegistro->codigo_titular }})
                                                </div>
                                                <div class="small mt-1">
                                                    Descuento convertido:
                                                    {{ number_format((float) $conciliacion->garanteRegistro->monto_aplicable, 2, ',', '.') }}
                                                    · Acumulado:
                                                    {{ number_format((float) $conciliacion->garanteRegistro->monto_acumulado, 2, ',', '.') }}
                                                    · Saldo pendiente:
                                                    {{ number_format((float) $conciliacion->garanteRegistro->saldo_pendiente, 2, ',', '.') }}
                                                </div>
                                                <div class="small mt-1">
                                                    {{ $conciliacion->garanteRegistro->observacion_sistema }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row g-2 mb-3">
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Monto asignado del Excel</div>
                                                    <div class="fw-bold fs-5">
                                                        {{ number_format((float) $conciliacion->monto_excel_asignado, 2, ',', '.') }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        {{
                                                            $conciliacion->concepto
                                                                === \App\Models\LotePrestamoConciliacion::CONCEPTO_GARANTE
                                                                    ? 'Comparado en Bs:'
                                                                    : 'Ajustado para comparar:'
                                                        }}
                                                        {{ number_format((float) $conciliacion->monto_excel, 2, ',', '.') }}
                                                    </div>
                                                    @if($conciliacion->registro)
                                                        <div class="text-muted small">
                                                            Total original del Excel:
                                                            {{ number_format((float) $conciliacion->registro->monto_descuento, 2, ',', '.') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Monto esperado</div>
                                                    <div class="fw-bold fs-5">
                                                        {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <div class="text-muted small">Diferencia</div>
                                                    <div class="fw-bold fs-5
                                                        {{ (float) $conciliacion->diferencia < -0.01
                                                            ? 'text-danger'
                                                            : ((float) $conciliacion->diferencia > 0.01
                                                                ? 'text-warning'
                                                                : 'text-success') }}"
                                                    >
                                                        {{ number_format((float) $conciliacion->diferencia, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($conciliacion->detalles->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Solicitud</th>
                                                            <th>Cuota</th>
                                                            <th>Tipo de préstamo</th>
                                                            <th>Grupo</th>
                                                            <th class="text-end">Cuota fija</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($conciliacion->detalles as $detalle)
                                                            <tr>
                                                                <td>{{ $detalle->id_solicitud }}</td>
                                                                <td>
                                                                    N.º {{ $detalle->nro_cuota }}
                                                                    <div class="text-muted small">
                                                                        ID {{ $detalle->id_cuota_solicitud }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    {{ $detalle->tipo_prestamo }}
                                                                    <div class="text-muted small">
                                                                        {{ $detalle->descripcion_tipo ?: 'Sin descripción' }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @if($detalle->grupo_comparacion)
                                                                        <span class="badge bg-secondary">
                                                                            {{ str_replace('_', ' ', $detalle->grupo_comparacion) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-info text-dark">
                                                                            NO CLASIFICADO
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end fw-semibold">
                                                                    {{ number_format((float) $detalle->monto_cuota, 2, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr class="fw-bold">
                                                            <td colspan="4" class="text-end">
                                                                {{
                                                                    $conciliacion->concepto
                                                                        === \App\Models\LotePrestamoConciliacion::CONCEPTO_GARANTE
                                                                            ? 'Monto informado por Cartera'
                                                                            : 'cuotas_solicitud.cuota_fija'
                                                                }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $conciliacion->monto_base_datos, 2, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center text-muted border rounded-3 py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                No existen cuotas asociadas para mostrar.
                                            </div>
                                        @endif
                                    </template>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    No existen resultados para el filtro seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($conciliaciones->hasPages())
                <div class="card-footer bg-white">
                    {{ $conciliaciones->links() }}
                </div>
            @endif
        </div>

    </div>

    @if($procesamientoPago)
        <div class="container-fluid">
            <div class="alert alert-success border-0 shadow-sm" role="status">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Pago mensual consolidado.</strong>
                Se generaron
                {{ number_format($procesamientoPago->cantidad_pagos) }}
                pagos por un total de
                {{ number_format((float) $procesamientoPago->monto_total, 2, ',', '.') }}.
                El lote está bloqueado para evitar pagos duplicados.
            </div>
        </div>
    @endif

    @if(! $procesamientoPago)
        <div
            class="modal fade"
            id="modalConfirmarPagoMensual"
            tabindex="-1"
            aria-labelledby="modalConfirmarPagoMensualTitulo"
            aria-hidden="true"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalConfirmarPagoMensualTitulo">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            ¡Atención! Confirmar pago mensual
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            Al confirmar se consolidarán definitivamente los
                            pagos válidos correspondientes a
                            <strong>{{ $lote->periodo }}</strong>. Solo se
                            registrarán las operaciones que estén
                            <strong class="text-success">COINCIDE</strong>; los
                            demás resultados serán ignorados.
                        </p>

                        @if($totalInconsistenciasPago > 0)
                            <div class="alert alert-danger border-danger mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                                    <div class="w-100">
                                        <div class="fw-bold mb-2">
                                            Se ignorarán
                                            {{ number_format($totalInconsistenciasPago) }}
                                            {{ $totalInconsistenciasPago === 1
                                                ? 'operación inconsistente'
                                                : 'operaciones inconsistentes' }}
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            @foreach($resumenInconsistenciasPago as $inconsistencia)
                                                <span class="badge bg-light text-danger border border-danger">
                                                    {{ $inconsistencia['etiqueta'] }}:
                                                    {{ number_format($inconsistencia['total']) }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <div class="small">
                                            Estas operaciones no generarán
                                            registros en <code>pagos</code> ni
                                            modificarán sus cuotas. Después de
                                            consolidar el lote, solo podrán
                                            resolverse individualmente mediante
                                            <strong>Préstamos → Realizar pago</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-success border-success mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                No existen operaciones inconsistentes en la
                                comparación actual.
                            </div>
                        @endif

                        @if($garantesPendientesPago > 0)
                            <div class="alert alert-info border-info mb-3">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Existen
                                <strong>{{ number_format($garantesPendientesPago) }}</strong>
                                descuentos a garantes conciliados que todavía
                                no completan la cuota del titular. Permanecerán
                                pendientes y no se registrarán como pagos.
                            </div>
                        @endif

                        <div class="alert alert-warning border-warning mb-3">
                            <div class="fw-bold text-uppercase mb-1">
                                <i class="bi bi-shield-exclamation me-1"></i>
                                Esta acción no se puede deshacer
                            </div>
                            Una vez procesado el lote, no será posible modificar
                            los montos ni eliminar los pagos generados desde esta
                            conciliación. La confirmación también implica que el
                            operador acepta omitir las inconsistencias indicadas.
                        </div>

                        <p class="text-muted small mb-0">
                            Si posteriormente debe registrar un pago adicional,
                            utilice la opción <strong>Realizar pago</strong> del
                            menú de Préstamos.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            <i class="bi bi-x-lg me-1"></i>
                            Cancelar
                        </button>

                        <form
                            id="formPagoMensual"
                            method="POST"
                            action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.pagar', $lote) }}"
                        >
                            @csrf
                            <button
                                type="submit"
                                id="btnConfirmarPagoMensual"
                                class="btn btn-success"
                            >
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Sí, ignorar inconsistencias y consolidar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($registrosGarantes->isNotEmpty())
        <div
            class="modal fade"
            id="modalSeguimientoGarantes"
            tabindex="-1"
            aria-labelledby="modalSeguimientoGarantesTitulo"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <div>
                            <h5 class="modal-title" id="modalSeguimientoGarantesTitulo">
                                <i class="bi bi-hourglass-split me-2"></i>
                                Seguimiento de descuentos a garantes
                            </h5>
                            <div class="small opacity-75">
                                La conciliación con el Excel y la aplicación al
                                titular se controlan por separado.
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Origen</th>
                                        <th>Garante</th>
                                        <th>Titular beneficiado</th>
                                        <th class="text-end">Monto Bs</th>
                                        <th class="text-end">Aplicable ÷ 6,96</th>
                                        <th>Solicitud / cuota</th>
                                        <th>Conciliación</th>
                                        <th>Aplicación</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registrosGarantes as $registroGarante)
                                        <tr>
                                            <td class="small">
                                                <div>{{ $registroGarante->archivo?->nombre_original }}</div>
                                                <div class="text-muted">
                                                    Fila {{ $registroGarante->fila_origen }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $registroGarante->nombre_garante }}
                                                </div>
                                                <div class="text-muted small">
                                                    Papeleta {{ $registroGarante->codigo_garante }}
                                                    · {{ $registroGarante->tipo_garante }}
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $registroGarante->nombre_titular }}</div>
                                                <div class="text-muted small">
                                                    Papeleta {{ $registroGarante->codigo_titular }}
                                                </div>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                {{ number_format((float) $registroGarante->monto_bs, 2, ',', '.') }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format((float) $registroGarante->monto_aplicable, 2, ',', '.') }}
                                            </td>
                                            <td>
                                                @if($registroGarante->id_solicitud)
                                                    <div>
                                                        Solicitud
                                                        {{ $registroGarante->id_solicitud }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        Cuota ID
                                                        {{ $registroGarante->id_cuota_solicitud }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Sin identificar</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{
                                                    $registroGarante->estado_conciliacion === 'COINCIDE'
                                                        ? 'bg-success'
                                                        : ($registroGarante->estado_conciliacion === 'SIN_DESCUENTO_EXCEL'
                                                            ? 'bg-dark'
                                                            : 'bg-warning text-dark')
                                                }}">
                                                    {{ str_replace('_', ' ', $registroGarante->estado_conciliacion) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $registroGarante->clase_aplicacion }}">
                                                    {{ str_replace('_', ' ', $registroGarante->estado_aplicacion) }}
                                                </span>
                                                @if($registroGarante->estado_aplicacion === 'PENDIENTE')
                                                    <div class="text-muted small mt-1">
                                                        Falta
                                                        {{ number_format((float) $registroGarante->saldo_pendiente, 2, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="small" style="min-width: 260px;">
                                                <div>{{ $registroGarante->observacion_sistema }}</div>
                                                @if($registroGarante->observacion_excel)
                                                    <div class="text-muted mt-1">
                                                        Excel:
                                                        {{ $registroGarante->observacion_excel }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <span class="text-muted small me-auto">
                            {{ number_format($registrosGarantes->count()) }} registros
                        </span>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            <i class="bi bi-x-lg me-1"></i>
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($archivosGarantes->isNotEmpty() && $puedeCargarGarantes)
        <div
            class="modal fade"
            id="modalLimpiarGarantes"
            tabindex="-1"
            aria-labelledby="modalLimpiarGarantesTitulo"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalLimpiarGarantesTitulo">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Limpiar descuentos a garantes
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            Se eliminarán los archivos de garantes cargados en
                            este lote y sus comparaciones.
                        </p>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            Esta acción no se puede deshacer. No estará disponible
                            si algún descuento ya fue aplicado.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>
                        <form
                            method="POST"
                            action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.conciliacion.garantes.limpiar', $lote) }}"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash3-fill me-1"></i>
                                Sí, limpiar garantes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div
        class="modal fade"
        id="modalDetalleConciliacion"
        tabindex="-1"
        aria-labelledby="modalDetalleConciliacionTitulo"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <div>
                        <h5 class="modal-title" id="modalDetalleConciliacionTitulo">
                            <i class="bi bi-file-earmark-text-fill me-2"></i>
                            Detalle de conciliación
                        </h5>
                        <div class="small opacity-75" id="modalDetalleConciliacionSubtitulo"></div>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>
                <div class="modal-body" id="modalDetalleConciliacionContenido">
                    <div class="text-center text-muted py-4">
                        Seleccione un registro para consultar su detalle.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="overlayCargandoGarantes"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
    >
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div
                class="spinner-border text-success mb-3"
                style="width: 3rem; height: 3rem;"
                aria-hidden="true"
            ></div>
            <h5 class="mb-1">Cargando y comparando garantes...</h5>
            <p class="text-muted mb-0">
                Espere mientras se reemplazan los datos anteriores, se importa
                el Excel y se recalcula la conciliación.
            </p>
        </div>
    </div>

    <div
        id="overlayComparandoPrestamos"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2000; background: rgba(15, 23, 42, .68);"
        role="status"
        aria-live="polite"
    >
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div
                class="spinner-border text-success mb-3"
                style="width: 3rem; height: 3rem;"
                aria-hidden="true"
            ></div>
            <h5 class="mb-1">Comparando préstamos...</h5>
            <p class="text-muted mb-0">
                Espere mientras se consultan y clasifican todos los registros.
            </p>
        </div>
    </div>

    <div
        id="overlayPagoMensual"
        class="d-none position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="z-index: 2100; background: rgba(15, 23, 42, .76);"
        role="status"
        aria-live="assertive"
    >
        <div class="bg-white rounded-4 shadow-lg px-5 py-4 text-center">
            <div
                class="spinner-border text-success mb-3"
                style="width: 3rem; height: 3rem;"
                aria-hidden="true"
            ></div>
            <h5 class="mb-1">Consolidando el pago mensual...</h5>
            <p class="text-muted mb-0">
                No cierre ni recargue esta página. Los pagos se están
                registrando y las cuotas se están actualizando.
            </p>
        </div>
    </div>

    {{-- Modal exclusivo de Préstamos, integrado para no depender de parciales externos. --}}
    @if(! $procesamientoPago)
        <div
            class="modal fade"
            id="modalOtrosArchivosPrestamos"
            tabindex="-1"
            aria-labelledby="modalOtrosArchivosPrestamosLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <form
                        id="formOtroArchivoPrestamos"
                        method="POST"
                        action="{{ route('procesamiento-mensual.lotes.archivos.prestamos.otros.store', $lote) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <input type="hidden" name="hash_preview" id="hashPreviewOtroArchivo">

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalOtrosArchivosPrestamosLabel">
                                <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>
                                Otros archivos de Préstamos
                            </h5>
                            <button
                                type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Cerrar"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Seleccione la planilla adicional
                                <strong>planilla_mindef_MM_AAAA.xlsx</strong>.
                                Para Préstamos solo se usarán las columnas
                                <strong>PRESTAMO</strong> y <strong>SER ADM</strong>.
                                Las columnas APORTE y FVS no se incorporarán en este proceso.
                            </div>

                            <div id="erroresOtroArchivo" class="alert alert-danger d-none" role="alert">
                                <div class="fw-semibold mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    No fue posible previsualizar la planilla
                                </div>
                                <ul class="mb-0 ps-3" id="listaErroresOtroArchivo"></ul>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-lg-9">
                                    <label for="archivoOtroPrestamo" class="form-label fw-semibold">
                                        Planilla adicional de MinDef
                                    </label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="archivoOtroPrestamo"
                                        name="archivo"
                                        accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                        required
                                    >
                                    <div class="form-text">
                                        Debe corresponder al período
                                        <strong>{{ $lote->codigo_periodo }}</strong>.
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <button
                                        type="button"
                                        class="btn btn-primary w-100"
                                        id="btnPrevisualizarOtroArchivo"
                                    >
                                        <i class="bi bi-eye-fill me-1"></i>
                                        Previsualizar
                                    </button>
                                </div>
                            </div>

                            <div id="vistaPreviaOtroArchivo" class="d-none mt-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="border rounded-3 p-3 h-100 bg-light">
                                            <div class="small text-muted">Filas a incorporar</div>
                                            <div class="fs-4 fw-bold" id="previewFilasOtroArchivo">0</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="border rounded-3 p-3 h-100 bg-light">
                                            <div class="small text-muted">Préstamo</div>
                                            <div class="fs-5 fw-bold" id="previewPrestamoOtroArchivo">Bs 0,00</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="border rounded-3 p-3 h-100 bg-light">
                                            <div class="small text-muted">Servicio administrativo</div>
                                            <div class="fs-5 fw-bold" id="previewComisionOtroArchivo">Bs 0,00</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="border rounded-3 p-3 h-100 bg-light">
                                            <div class="small text-muted">Total neto</div>
                                            <div class="fs-5 fw-bold" id="previewNetoOtroArchivo">Bs 0,00</div>
                                        </div>
                                    </div>
                                </div>

                                <div id="observacionesOtroArchivo" class="alert alert-warning d-none"></div>

                                <div class="table-responsive border rounded-3">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fila</th>
                                                <th>Papeleta</th>
                                                <th>CI</th>
                                                <th>Grado</th>
                                                <th>Nombres</th>
                                                <th>Destino</th>
                                                <th class="text-end">Préstamo</th>
                                                <th class="text-end">Ser. Adm.</th>
                                                <th class="text-end">Neto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaPreviewOtroArchivo"></tbody>
                                    </table>
                                </div>

                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Revise los datos antes de confirmar. Al incorporarlos se
                                    recalculará toda la comparación de Préstamos y se buscará
                                    la deuda correspondiente de cada papeleta en la base de datos.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="btn btn-success"
                                id="btnConfirmarOtroArchivo"
                                disabled
                            >
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Confirmar e incorporar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modalElemento = document.getElementById('modalOtrosArchivosPrestamos');
                    const formulario = document.getElementById('formOtroArchivoPrestamos');
                    const archivo = document.getElementById('archivoOtroPrestamo');
                    const botonPreview = document.getElementById('btnPrevisualizarOtroArchivo');
                    const botonConfirmar = document.getElementById('btnConfirmarOtroArchivo');
                    const hashPreview = document.getElementById('hashPreviewOtroArchivo');
                    const vistaPrevia = document.getElementById('vistaPreviaOtroArchivo');
                    const errores = document.getElementById('erroresOtroArchivo');
                    const listaErrores = document.getElementById('listaErroresOtroArchivo');
                    const tabla = document.getElementById('tablaPreviewOtroArchivo');
                    const observaciones = document.getElementById('observacionesOtroArchivo');
                    const urlPreview = @json(route('procesamiento-mensual.lotes.archivos.prestamos.otros.preview', $lote));
                    const csrf = formulario.querySelector('input[name="_token"]').value;
                    const moneda = new Intl.NumberFormat('es-BO', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });

                    function reiniciarPreview() {
                        hashPreview.value = '';
                        botonConfirmar.disabled = true;
                        vistaPrevia.classList.add('d-none');
                        errores.classList.add('d-none');
                        listaErrores.replaceChildren();
                        tabla.replaceChildren();
                        observaciones.classList.add('d-none');
                        observaciones.textContent = '';
                    }

                    function mostrarErrores(mensajes) {
                        listaErrores.replaceChildren();

                        mensajes.forEach(function (mensaje) {
                            const item = document.createElement('li');
                            item.textContent = mensaje;
                            listaErrores.appendChild(item);
                        });

                        errores.classList.remove('d-none');
                    }

                    function agregarCelda(fila, valor, clase = '') {
                        const celda = document.createElement('td');
                        celda.textContent = valor ?? '';
                        if (clase) celda.className = clase;
                        fila.appendChild(celda);
                    }

                    archivo.addEventListener('change', reiniciarPreview);

                    botonPreview.addEventListener('click', async function () {
                        reiniciarPreview();

                        if (! archivo.files.length) {
                            mostrarErrores(['Seleccione la planilla adicional de MinDef.']);
                            return;
                        }

                        const textoOriginal = botonPreview.innerHTML;
                        botonPreview.disabled = true;
                        botonPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Revisando...';

                        try {
                            const datosFormulario = new FormData();
                            datosFormulario.append('_token', csrf);
                            datosFormulario.append('archivo', archivo.files[0]);

                            const respuesta = await fetch(urlPreview, {
                                method: 'POST',
                                body: datosFormulario,
                                headers: { 'Accept': 'application/json' },
                            });
                            const datos = await respuesta.json();

                            if (! respuesta.ok) {
                                const mensajes = datos.errors
                                    ? Object.values(datos.errors).flat()
                                    : [datos.message || 'No se pudo revisar la planilla.'];
                                mostrarErrores(mensajes);
                                return;
                            }

                            hashPreview.value = datos.hash;
                            document.getElementById('previewFilasOtroArchivo').textContent = datos.filas;
                            document.getElementById('previewPrestamoOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_prestamo);
                            document.getElementById('previewComisionOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_comision);
                            document.getElementById('previewNetoOtroArchivo').textContent = 'Bs ' + moneda.format(datos.total_neto);

                            datos.registros.forEach(function (registro) {
                                const fila = document.createElement('tr');
                                agregarCelda(fila, registro.fila);
                                agregarCelda(fila, registro.papeleta);
                                agregarCelda(fila, registro.carnet);
                                agregarCelda(fila, registro.grado);
                                agregarCelda(fila, registro.nombres);
                                agregarCelda(fila, registro.destino);
                                agregarCelda(fila, moneda.format(registro.prestamo), 'text-end');
                                agregarCelda(fila, moneda.format(registro.ser_adm), 'text-end');
                                agregarCelda(fila, moneda.format(registro.neto), 'text-end');
                                tabla.appendChild(fila);
                            });

                            const notas = [];
                            if (datos.omitidas_sin_prestamo > 0) {
                                notas.push(datos.omitidas_sin_prestamo + ' fila(s) con PRESTAMO y SER ADM en cero fueron omitidas.');
                            }
                            if (datos.duplicadas.length > 0) {
                                notas.push('Papeletas ya presentes y no incorporadas: ' + datos.duplicadas.join(', ') + '.');
                            }
                            if (notas.length > 0) {
                                observaciones.textContent = notas.join(' ');
                                observaciones.classList.remove('d-none');
                            }

                            vistaPrevia.classList.remove('d-none');
                            botonConfirmar.disabled = false;
                        } catch (error) {
                            mostrarErrores(['Ocurrió un error de comunicación al revisar la planilla.']);
                        } finally {
                            botonPreview.disabled = false;
                            botonPreview.innerHTML = textoOriginal;
                        }
                    });

                    formulario.addEventListener('submit', function (evento) {
                        if (! hashPreview.value) {
                            evento.preventDefault();
                            mostrarErrores(['Primero debe previsualizar y revisar la planilla.']);
                            return;
                        }

                        botonConfirmar.disabled = true;
                        botonConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Incorporando...';
                    });

                    @if($errors->has('hash_preview'))
                        bootstrap.Modal.getOrCreateInstance(modalElemento).show();
                    @endif
                });
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formulario = document.getElementById('formRecompararPrestamos');
                const boton = document.getElementById('btnRecompararPrestamos');
                const overlay = document.getElementById('overlayComparandoPrestamos');
                const modalDetalle = document.getElementById('modalDetalleConciliacion');
                const modalSubtitulo = document.getElementById('modalDetalleConciliacionSubtitulo');
                const modalContenido = document.getElementById('modalDetalleConciliacionContenido');
                const formularioGarantes = document.getElementById('formCargaGarantes');
                const botonGarantes = document.getElementById('btnCargarGarantes');
                const overlayGarantes = document.getElementById('overlayCargandoGarantes');
                const formularioPago = document.getElementById('formPagoMensual');
                const botonPago = document.getElementById('btnConfirmarPagoMensual');
                const overlayPago = document.getElementById('overlayPagoMensual');

                if (formulario && boton && overlay) {
                    formulario.addEventListener('submit', function () {
                        boton.disabled = true;
                        overlay.classList.remove('d-none');
                        overlay.classList.add('d-flex');
                    });
                }

                if (formularioGarantes && botonGarantes && overlayGarantes) {
                    formularioGarantes.addEventListener('submit', function () {
                        botonGarantes.disabled = true;
                        overlayGarantes.classList.remove('d-none');
                        overlayGarantes.classList.add('d-flex');
                    });
                }

                if (formularioPago && botonPago && overlayPago) {
                    formularioPago.addEventListener('submit', function (event) {
                        if (formularioPago.dataset.enviado === '1') {
                            event.preventDefault();
                            return;
                        }

                        formularioPago.dataset.enviado = '1';
                        botonPago.disabled = true;
                        botonPago.innerHTML = `
                            <span
                                class="spinner-border spinner-border-sm me-1"
                                aria-hidden="true"
                            ></span>
                            Consolidando...
                        `;
                        overlayPago.classList.remove('d-none');
                        overlayPago.classList.add('d-flex');
                    });
                }

                if (modalDetalle && modalSubtitulo && modalContenido) {
                    modalDetalle.addEventListener('show.bs.modal', function (event) {
                        const botonDetalle = event.relatedTarget;
                        const templateId = botonDetalle?.dataset.detalleTemplate;
                        const template = templateId
                            ? document.getElementById(templateId)
                            : null;

                        modalSubtitulo.textContent = botonDetalle?.dataset.codigoPersonal
                            ? `CODIGO_PERSONAL: ${botonDetalle.dataset.codigoPersonal}`
                            : '';

                        if (template instanceof HTMLTemplateElement) {
                            modalContenido.replaceChildren(
                                template.content.cloneNode(true)
                            );
                            return;
                        }

                        modalContenido.innerHTML = `
                            <div class="alert alert-warning mb-0">
                                No fue posible cargar el detalle seleccionado.
                            </div>
                        `;
                    });

                    modalDetalle.addEventListener('hidden.bs.modal', function () {
                        modalSubtitulo.textContent = '';
                        modalContenido.replaceChildren();
                    });
                }

                window.addEventListener('pageshow', function () {
                    if (botonGarantes && overlayGarantes) {
                        botonGarantes.disabled = false;
                        overlayGarantes.classList.add('d-none');
                        overlayGarantes.classList.remove('d-flex');
                    }

                    if (boton && overlay) {
                        boton.disabled = boton.dataset.bloqueado === '1';
                        overlay.classList.add('d-none');
                        overlay.classList.remove('d-flex');
                    }

                    if (formularioPago && botonPago && overlayPago) {
                        delete formularioPago.dataset.enviado;
                        botonPago.disabled = false;
                        botonPago.innerHTML = `
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Sí, ignorar inconsistencias y consolidar
                        `;
                        overlayPago.classList.add('d-none');
                        overlayPago.classList.remove('d-flex');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
