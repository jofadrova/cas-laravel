<x-app-layout>

    <x-slot name="header">
        Reportes de Socios
    </x-slot>

    {{-- APORTES Y CERTIFICADOS --}}
    <h4 class="mb-3 text-primary">
        <i class="fas fa-coins me-2"></i>
        Aportes y Certificados
    </h4>

    <div class="row g-4 mb-5">

        @php
            $reportesAportes = [
                ['icono' => 'fas fa-chart-line', 'titulo' => 'Altas y Bajas', 'descripcion' => 'Reporte de altas y bajas por mes y gestión.'],
                ['icono' => 'fas fa-coins', 'titulo' => 'Capitalización 5%', 'descripcion' => 'Total de aportes global por gestión y asociados.'],
                ['icono' => 'fas fa-money-bill-wave', 'titulo' => 'Aportes 3%', 'descripcion' => 'Total de aportes por gestión y asociados.'],
                ['icono' => 'fas fa-piggy-bank', 'titulo' => 'Intereses Mensuales', 'descripcion' => 'Aportes voluntarios por gestión, mes y asociados.'],
                ['icono' => 'fas fa-certificate', 'titulo' => 'Certificados', 'descripcion' => 'Reporte total de certificados por tipo y asociado.'],
            ];
        @endphp

        @foreach($reportesAportes as $reporte)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="{{ $reporte['icono'] }} text-primary me-2"></i>
                            {{ $reporte['titulo'] }}
                        </h5>

                        <p class="card-text text-muted">
                            {{ $reporte['descripcion'] }}
                        </p>
                    </div>

                    <div class="card-footer border-0">
                        <button class="btn btn-outline-secondary btn-sm" disabled>
                            Próximamente
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- PRESTAMOS --}}
    <h4 class="mb-3 text-success">
        <i class="fas fa-landmark me-2"></i>
        Préstamos
    </h4>

    <div class="row g-4 mb-5">

        @php
            $reportesPrestamos = [
                ['icono' => 'fas fa-landmark', 'titulo' => 'Tipo de Préstamo', 'descripcion' => 'Préstamos por estado y gestión.'],
                ['icono' => 'fas fa-file-invoice-dollar', 'titulo' => 'Nuevos Préstamos', 'descripcion' => 'Nuevos préstamos por mes.(ENVIO MINDEF) '],
                ['icono' => 'fas fa-clock', 'titulo' => 'Cuotas Pendientes', 'descripcion' => 'Reporte de cuotas pendientes.'],
                ['icono' => 'fas fa-chart-pie', 'titulo' => 'Interés Capital', 'descripcion' => 'Reporte de interés capital.'],
                ['icono' => 'fas fa-handshake', 'titulo' => 'Garantes', 'descripcion' => 'Garantes por número de papeleta.'],
                ['icono' => 'fas fa-file-alt', 'titulo' => 'Extracto Préstamos', 'descripcion' => 'Extracto de préstamos por papeleta.'],
                ['icono' => 'fas fa-check-circle', 'titulo' => 'Préstamos Completados', 'descripcion' => 'Préstamos con cuotas completas.'],
            ];
        @endphp

        @foreach($reportesPrestamos as $reporte)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="{{ $reporte['icono'] }} text-success me-2"></i>
                            {{ $reporte['titulo'] }}
                        </h5>

                        <p class="card-text text-muted">
                            {{ $reporte['descripcion'] }}
                        </p>
                    </div>

                    <div class="card-footer border-0">
                        <button class="btn btn-outline-secondary btn-sm" disabled>
                            Próximamente
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- SOCIOS --}}
    <h4 class="mb-3 text-info">
        <i class="fas fa-users me-2"></i>
        Socios
    </h4>
    <div class="row g-4 mb-5">
        @php
            $reportesSocios = [
                ['icono' => 'fas fa-user-graduate', 'titulo' => 'Promociones', 'descripcion' => 'Socios por año de promoción.'],
                ['icono' => 'fas fa-user-plus', 'titulo' => 'Nuevos Afiliados Gestión', 'descripcion' => 'Nuevos afiliados por gestión.'],
                ['icono' => 'fas fa-calendar-plus', 'titulo' => 'Nuevos Afiliados Mes', 'descripcion' => 'Nuevos afiliados por mes y gestión.'],
            ];
        @endphp

        @foreach($reportesSocios as $reporte)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="{{ $reporte['icono'] }} text-info me-2"></i>
                            {{ $reporte['titulo'] }}
                        </h5>

                        <p class="card-text text-muted">
                            {{ $reporte['descripcion'] }}
                        </p>
                    </div>

                    <div class="card-footer border-0">
                        <button class="btn btn-outline-secondary btn-sm" disabled>
                            Próximamente
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{-- KARDEX --}}
    <h4 class="mb-3 text-warning">
        <i class="fas fa-book me-2"></i>
        Kardex
    </h4>
    <div class="row g-4">
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-book text-warning me-2"></i>
                        Kardex CAS
                    </h5>
                    <p class="card-text text-muted">
                        Kardex Conta - Cartera consolidado.
                    </p>
                </div>
                <div class="card-footer border-0">
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        Próximamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>