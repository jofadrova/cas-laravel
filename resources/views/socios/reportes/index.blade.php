<x-app-layout>

    <x-slot name="header">
        Reportes
    </x-slot>

    <div class="container-fluid py-4">

        {{-- APORTES Y CERTIFICADOS --}}
        <div class="d-flex align-items-center mb-3">
            <div class="report-section-icon bg-primary-subtle text-primary me-3">
                <i class="fas fa-coins"></i>
            </div>

            <div>
                <h4 class="mb-0 fw-bold text-primary">
                    Aportes y Certificados
                </h4>
                <small class="text-muted">
                    Reportes de aportes, capitalización y certificados.
                </small>
            </div>
        </div>

        <div class="row g-4 mb-5">

            @php
                $reportesAportes = [
                    [
                        'icono' => 'fas fa-chart-line',
                        'titulo' => 'Altas y Bajas',
                        'descripcion' => 'Altas y bajas de asociados por mes y gestión.',
                    ],
                    [
                        'icono' => 'fas fa-coins',
                        'titulo' => 'Capitalización 5%',
                        'descripcion' => 'Total de aportes de capitalización por gestión y asociado.',
                    ],
                    [
                        'icono' => 'fas fa-money-bill-wave',
                        'titulo' => 'Aportes 3%',
                        'descripcion' => 'Total de aportes institucionales por gestión y asociado.',
                    ],
                    [
                        'icono' => 'fas fa-piggy-bank',
                        'titulo' => 'Intereses Mensuales',
                        'descripcion' => 'Aportes voluntarios por gestión, mes y asociado.',
                    ],
                    [
                        'icono' => 'fas fa-certificate',
                        'titulo' => 'Certificados',
                        'descripcion' => 'Resumen de certificados por tipo y asociado.',
                    ],
                ];
            @endphp

            @foreach($reportesAportes as $reporte)
                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="report-icon bg-primary-subtle text-primary mb-3">
                                <i class="{{ $reporte['icono'] }}"></i>
                            </div>

                            <h5 class="card-title fw-bold mb-2">
                                {{ $reporte['titulo'] }}
                            </h5>

                            <p class="card-text text-muted mb-0">
                                {{ $reporte['descripcion'] }}
                            </p>
                        </div>

                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    disabled>
                                <i class="fas fa-clock me-1"></i>
                                Próximamente
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- PRÉSTAMOS --}}
        <div class="d-flex align-items-center mb-3">
            <div class="report-section-icon bg-success-subtle text-success me-3">
                <i class="fas fa-landmark"></i>
            </div>

            <div>
                <h4 class="mb-0 fw-bold text-success">
                    Préstamos
                </h4>
                <small class="text-muted">
                    Reportes ejecutivos y operativos de la cartera de préstamos.
                </small>
            </div>
        </div>

        <div class="row g-4 mb-5">

            {{-- REPORTE HABILITADO: TIPOS DE PRÉSTAMO --}}
            <div class="col-md-6 col-xl-4">
                <div class="card report-card report-card-active h-100 shadow-sm border-0">

                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="report-icon bg-success-subtle text-success">
                                <i class="fas fa-chart-bar"></i>
                            </div>

                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>
                                Disponible
                            </span>
                        </div>

                        <h5 class="card-title fw-bold mb-2">
                            Tipos de Préstamo
                        </h5>

                        <p class="card-text text-muted mb-0">
                            Reporte ejecutivo de préstamos agrupados por tipo, estado y gestión.
                        </p>

                    </div>

                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                        <a href="{{ route('prestamos.reportes.tipos-prestamo') }}"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>
                            Ver reporte
                        </a>
                    </div>

                </div>
            </div>

            @php
                $reportesPrestamos = [
                    [
                        'icono' => 'fas fa-file-invoice-dollar',
                        'titulo' => 'Nuevos Préstamos',
                        'descripcion' => 'Nuevos préstamos por mes y gestión para el envío al Ministerio de Defensa.',
                    ],
                    [
                        'icono' => 'fas fa-clock',
                        'titulo' => 'Cuotas Pendientes',
                        'descripcion' => 'Detalle de cuotas pendientes de pago por préstamo y asociado.',
                    ],
                    [
                        'icono' => 'fas fa-chart-pie',
                        'titulo' => 'Interés y Capital',
                        'descripcion' => 'Resumen del capital amortizado y los intereses cobrados.',
                    ],
                    [
                        'icono' => 'fas fa-handshake',
                        'titulo' => 'Garantes',
                        'descripcion' => 'Consulta de garantes por número de papeleta y préstamo.',
                    ],
                    [
                        'icono' => 'fas fa-file-alt',
                        'titulo' => 'Extracto de Préstamos',
                        'descripcion' => 'Extracto consolidado de préstamos por número de papeleta.',
                    ],
                    [
                        'icono' => 'fas fa-check-circle',
                        'titulo' => 'Préstamos Completados',
                        'descripcion' => 'Préstamos con todas sus cuotas pagadas y saldo cancelado.',
                    ],
                ];
            @endphp

            @foreach($reportesPrestamos as $reporte)
                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="report-icon bg-success-subtle text-success mb-3">
                                <i class="{{ $reporte['icono'] }}"></i>
                            </div>

                            <h5 class="card-title fw-bold mb-2">
                                {{ $reporte['titulo'] }}
                            </h5>

                            <p class="card-text text-muted mb-0">
                                {{ $reporte['descripcion'] }}
                            </p>
                        </div>

                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    disabled>
                                <i class="fas fa-clock me-1"></i>
                                Próximamente
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- SOCIOS --}}
        <div class="d-flex align-items-center mb-3">
            <div class="report-section-icon bg-info-subtle text-info me-3">
                <i class="fas fa-users"></i>
            </div>

            <div>
                <h4 class="mb-0 fw-bold text-info">
                    Socios
                </h4>
                <small class="text-muted">
                    Reportes estadísticos y de afiliación de los asociados.
                </small>
            </div>
        </div>

        <div class="row g-4 mb-5">

            @php
                $reportesSocios = [
                    [
                        'icono' => 'fas fa-user-graduate',
                        'titulo' => 'Promociones',
                        'descripcion' => 'Distribución de socios por año de promoción.',
                    ],
                    [
                        'icono' => 'fas fa-user-plus',
                        'titulo' => 'Nuevos Afiliados por Gestión',
                        'descripcion' => 'Nuevos asociados registrados durante una gestión.',
                    ],
                    [
                        'icono' => 'fas fa-calendar-plus',
                        'titulo' => 'Nuevos Afiliados por Mes',
                        'descripcion' => 'Nuevos asociados registrados por mes y gestión.',
                    ],
                ];
            @endphp

            @foreach($reportesSocios as $reporte)
                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="report-icon bg-info-subtle text-info mb-3">
                                <i class="{{ $reporte['icono'] }}"></i>
                            </div>

                            <h5 class="card-title fw-bold mb-2">
                                {{ $reporte['titulo'] }}
                            </h5>

                            <p class="card-text text-muted mb-0">
                                {{ $reporte['descripcion'] }}
                            </p>
                        </div>

                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    disabled>
                                <i class="fas fa-clock me-1"></i>
                                Próximamente
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- KARDEX --}}
        <div class="d-flex align-items-center mb-3">
            <div class="report-section-icon bg-warning-subtle text-warning me-3">
                <i class="fas fa-book"></i>
            </div>

            <div>
                <h4 class="mb-0 fw-bold text-warning">
                    Kardex
                </h4>
                <small class="text-muted">
                    Reportes consolidados de cartera, contabilidad y asociados.
                </small>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-md-6 col-xl-4">
                <div class="card report-card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="report-icon bg-warning-subtle text-warning mb-3">
                            <i class="fas fa-book"></i>
                        </div>

                        <h5 class="card-title fw-bold mb-2">
                            Kardex CAS
                        </h5>

                        <p class="card-text text-muted mb-0">
                            Kardex consolidado de contabilidad, cartera y socios.
                        </p>
                    </div>

                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                disabled>
                            <i class="fas fa-clock me-1"></i>
                            Próximamente
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('styles')
        <style>
            .report-card {
                border-radius: 1rem;
                transition: transform .2s ease, box-shadow .2s ease;
            }

            .report-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 .85rem 2rem rgba(0, 0, 0, .10) !important;
            }

            .report-card-active {
                border-left: 4px solid var(--bs-success) !important;
            }

            .report-icon,
            .report-section-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .report-icon {
                width: 52px;
                height: 52px;
                border-radius: 15px;
                font-size: 1.35rem;
            }

            .report-section-icon {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                font-size: 1.15rem;
            }

            .report-card .card-text {
                line-height: 1.55;
            }

            .report-card .card-footer {
                margin-top: auto;
            }
        </style>
    @endpush

</x-app-layout>