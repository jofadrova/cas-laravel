<x-app-layout>
    <x-slot name="header">Reporte Ejecutivo por Tipo de Préstamo</x-slot>
    <div class="container-fluid px-0">
        <div class="report-hero shadow-sm mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="hero-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1">Cartera por Tipo de Préstamo</h2>
                            <p class="mb-0 opacity-75">
                                Análisis ejecutivo de colocaciones, saldos,
                                recuperación y estado de la cartera.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('socios.reportes') }}"
                       class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver a reportes
                    </a>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('prestamos.reportes.tipos-prestamo') }}"class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="gestion" class="form-label fw-semibold">Gestión</label>
                        <select name="gestion" id="gestion"class="form-select">
                        <option value="">Todas las gestiones</option>
                        @foreach($gestiones as $gestionItem)
                            <option value="{{ $gestionItem }}"
                                @selected((int) $gestionSeleccionada === (int) $gestionItem)>
                                Gestión {{ $gestionItem }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="estado" class="form-label fw-semibold">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="AC" @selected($estadoSeleccionado === 'AC')>Activos</option>
                            <option value="PA" @selected($estadoSeleccionado === 'PA')>Pagados</option>
                            <option value="CE" @selected($estadoSeleccionado === 'CE')>Cerrados por refinanciamiento</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tipo_prestamo" class="form-label fw-semibold">Tipo de préstamo</label>
                        <select name="tipo_prestamo" id="tipo_prestamo" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach($catalogoTipos as $tipo)
                                <option value="{{ $tipo->id_tasa }}"
                                    @selected($tipoSeleccionado == $tipo->id_tasa)>
                                    {{ $tipo->descripcion_tasa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('prestamos.reportes.tipos-prestamo') }}"
                               class="btn btn-outline-secondary">
                                <i class="fas fa-rotate-left me-1"></i>
                                Limpiar
                            </a>
                            <button type="submit" class="btn btn-success"> <i class="fas fa-filter me-1"></i>Aplicar filtros</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card indicator-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="indicator-label">
                                    Total de préstamos
                                </div>
                                <div class="indicator-value">
                                    {{ number_format($indicadores->total_prestamos ?? 0) }}
                                </div>
                                <div class="indicator-description">
                                    Operaciones registradas
                                </div>
                            </div>
                            <div class="indicator-icon bg-primary-subtle text-primary">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card indicator-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="indicator-label">
                                    Monto colocado
                                </div>
                                <div class="indicator-money">
                                    @forelse($indicadoresPorMoneda as $moneda)
                                        <div>
                                            {{ $moneda['simbolo'] }}
                                            {{ number_format($moneda['monto_colocado'], 2, ',', '.') }}
                                        </div>
                                    @empty
                                        <div>Bs 0,00</div>
                                    @endforelse
                                </div>
                                <div class="indicator-description">
                                    Capital originalmente desembolsado
                                </div>
                            </div>
                            <div class="indicator-icon bg-success-subtle text-success">
                                <i class="fas fa-money-bill-trend-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card indicator-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="indicator-label">
                                    Saldo de cartera
                                </div>
                                <div class="indicator-money text-warning-emphasis">
                                    @forelse($indicadoresPorMoneda as $moneda)
                                        <div>
                                            {{ $moneda['simbolo'] }}
                                            {{ number_format($moneda['saldo_actual'], 2, ',', '.') }}
                                        </div>
                                    @empty
                                        <div>Bs 0,00</div>
                                    @endforelse
                                </div>
                                <div class="indicator-description">
                                    Capital pendiente de recuperación
                                </div>
                            </div>
                            <div class="indicator-icon bg-warning-subtle text-warning">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card indicator-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="indicator-label">
                                    Capital recuperado
                                </div>
                                <div class="indicator-money text-info-emphasis">
                                    @forelse($indicadoresPorMoneda as $moneda)
                                        <div>
                                            {{ $moneda['simbolo'] }}
                                            {{ number_format($moneda['capital_recuperado'], 2, ',', '.') }}
                                        </div>
                                    @empty
                                        <div>Bs 0,00</div>
                                    @endforelse
                                </div>
                                <div class="indicator-description">
                                    Diferencia entre colocado y saldo
                                </div>
                            </div>
                            <div class="indicator-icon bg-info-subtle text-info">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($monedaMixta)
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        Los resultados incluyen préstamos en <strong>bolivianos</strong> y
                        <strong>dólares estadounidenses</strong>. Por esa razón, los indicadores
                        generales se muestran sin un símbolo monetario único. Seleccione un tipo
                        de préstamo para ver sus importes con <strong>Bs</strong> o <strong>$us</strong>.
                    </div>
                </div>
            </div>
        @endif
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="state-card state-active shadow-sm">
                    <div>
                        <span class="state-number">
                            {{ number_format($indicadores->activos ?? 0) }}
                        </span>
                        <span class="state-label">
                            Préstamos activos
                        </span>
                    </div>
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="state-card state-paid shadow-sm">
                    <div>
                        <span class="state-number">
                            {{ number_format($indicadores->pagados ?? 0) }}
                        </span>
                        <span class="state-label">
                            Préstamos pagados
                        </span>
                    </div>
                    <i class="fas fa-flag-checkered"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="state-card state-refinanced shadow-sm">
                    <div>
                        <span class="state-number">
                            {{ number_format($indicadores->refinanciados ?? 0) }}
                        </span>
                        <span class="state-label">
                            Cerrados por refinanciamiento
                        </span>
                    </div>
                    <i class="fas fa-arrows-rotate"></i>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1">
                            Colocación y saldo por tipo
                        </h5>
                        <p class="text-muted small mb-0">
                            Comparación entre capital desembolsado y saldo pendiente.
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container">
                            <canvas id="chartTiposPrestamo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-1">Estado de operaciones</h5>
                        <p class="text-muted small mb-0">
                            Distribución general de préstamos.
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container chart-container-small">
                            <canvas id="chartEstadosPrestamo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Resumen por tipo de préstamo</h5>
                        <p class="text-muted small mb-0">
                            Resultados financieros y operativos consolidados.
                        </p>
                    </div>
                    <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                        {{ $tiposPrestamo->count() }}
                        tipos encontrados
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tipo de préstamo</th>
                            <th class="text-center">Operaciones</th>
                            <th class="text-center">Estados</th>
                            <th class="text-end">Monto colocado</th>
                            <th class="text-end">Saldo</th>
                            <th class="text-end">Recuperado</th>
                            <th class="text-center pe-4">Participación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiposPrestamo as $tipo)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">
                                        {{ $tipo->descripcion_tasa }}
                                    </div>
                                    <div class="small text-muted">
                                        Tasa:
                                        {{ number_format($tipo->porcentaje, 2) }}%

                                        ·

                                        {{ $tipo->nombre_moneda }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-5">
                                        {{ number_format($tipo->total_prestamos) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-1">
                                        <span class="badge bg-success-subtle text-success"
                                              title="Activos">
                                            AC: {{ $tipo->activos }}
                                        </span>
                                        <span class="badge bg-primary-subtle text-primary"
                                              title="Pagados">
                                            PA: {{ $tipo->pagados }}
                                        </span>
                                        <span class="badge bg-warning-subtle text-warning"
                                              title="Cerrados por refinanciamiento">
                                            CE: {{ $tipo->refinanciados }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ $tipo->simbolo_moneda }}
                                    {{ number_format($tipo->monto_colocado,2,',','.') }}
                                </td>
                                <td class="text-end text-warning-emphasis fw-semibold">
                                    {{ $tipo->simbolo_moneda }}
                                    {{ number_format($tipo->saldo_actual,2,',','.') }}
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ $tipo->simbolo_moneda }}
                                    {{ number_format($tipo->capital_recuperado,2,',','.') }}
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>
                                            Participación
                                        </span>
                                        <strong>
                                            {{ number_format($tipo->participacion, 2) }}%
                                        </strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"style="width: {{ min($tipo->participacion, 100) }}%">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-chart-column fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No existen resultados</h5>
                                    <p class="text-muted mb-0">
                                        No se encontraron préstamos con los filtros seleccionados.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @push('styles')
        <style>
            .report-hero {
                padding: 2rem;
                border-radius: 1rem;
                color: white;
                background:
                    linear-gradient(
                        135deg,
                        #146c43 0%,
                        #198754 55%,
                        #20a66a 100%
                    );
            }

            .hero-icon {
                width: 68px;
                height: 68px;
                border-radius: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 1.8rem;
                background: rgba(255, 255, 255, .18);
                backdrop-filter: blur(5px);
            }

            .indicator-card {
                border-radius: 1rem;
                transition:
                    transform .2s ease,
                    box-shadow .2s ease;
            }

            .indicator-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 .8rem 2rem rgba(0, 0, 0, .10) !important;
            }

            .indicator-label {
                color: var(--bs-secondary-color);
                font-size: .75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04rem;
                margin-bottom: .5rem;
            }

            .indicator-value {
                font-size: 2.2rem;
                font-weight: 800;
                line-height: 1.15;
            }

            .indicator-money {
                font-size: 1.45rem;
                font-weight: 800;
                line-height: 1.25;
            }

            .indicator-description {
                color: var(--bs-secondary-color);
                font-size: .78rem;
                margin-top: .6rem;
            }

            .indicator-icon {
                width: 52px;
                height: 52px;
                min-width: 52px;
                border-radius: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
            }

            .state-card {
                min-height: 105px;
                padding: 1.5rem;
                border-radius: 1rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: white;
            }

            .state-card > i {
                font-size: 2rem;
                opacity: .6;
            }

            .state-number,
            .state-label {
                display: block;
            }

            .state-number {
                font-size: 2rem;
                font-weight: 800;
                line-height: 1;
                margin-bottom: .45rem;
            }

            .state-label {
                font-size: .85rem;
                opacity: .85;
            }

            .state-active {
                background: linear-gradient(135deg, #146c43, #198754);
            }

            .state-paid {
                background: linear-gradient(135deg, #084298, #0d6efd);
            }

            .state-refinanced {
                background: linear-gradient(135deg, #997404, #ffc107);
            }

            .chart-container {
                position: relative;
                min-height: 360px;
            }

            .chart-container-small {
                min-height: 320px;
            }

            .table th {
                white-space: nowrap;
                font-size: .78rem;
                text-transform: uppercase;
                color: var(--bs-secondary-color);
            }

            @media (max-width: 767.98px) {
                .report-hero {
                    padding: 1.5rem;
                }

                .hero-icon {
                    width: 55px;
                    height: 55px;
                }

                .indicator-money {
                    font-size: 1.2rem;
                }
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const simboloMoneda = @json($simboloMoneda ?? '');
                const labelsTipos = @json($graficoTipos['labels']);
                const montosTipos = @json($graficoTipos['montos']);
                const saldosTipos = @json($graficoTipos['saldos']);
                const simbolosTipos = @json($graficoTipos['simbolos']);
                const simboloIndicadores = @json($simboloMonedaIndicadores ?? '');

                const labelsEstados = @json($graficoEstados['labels']);
                const datosEstados = @json($graficoEstados['datos']);

                const canvasTipos = document.getElementById('chartTiposPrestamo');
                const canvasEstados = document.getElementById('chartEstadosPrestamo');

                if (canvasTipos) {
                    new Chart(canvasTipos, {
                        type: 'bar',

                        data: {
                            labels: labelsTipos,

                            datasets: [
                                {
                                    label: 'Monto colocado',
                                    data: montosTipos,
                                    backgroundColor: 'rgba(25, 135, 84, .75)',
                                    borderColor: 'rgba(25, 135, 84, 1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Saldo actual',
                                    data: saldosTipos,
                                    backgroundColor: 'rgba(255, 193, 7, .75)',
                                    borderColor: 'rgba(255, 193, 7, 1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                },
                            ],
                        },

                        options: {
                            responsive: true,
                            maintainAspectRatio: false,

                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },

                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const simbolo = simbolosTipos[context.dataIndex] ?? '';

                                            return context.dataset.label
                                                + ': '
                                                + simbolo
                                                + ' '
                                                + Number(context.raw).toLocaleString(
                                                'es-BO',
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                }
                                            );
                                        },
                                    },
                                },
                            },

                            scales: {
                                y: {
                                    beginAtZero: true,

                                    ticks: {
                                        callback: function (value) {
                                            return (simboloIndicadores
                                                    ? simboloIndicadores + ' '
                                                    : '')
                                                + Number(value).toLocaleString('es-BO');
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                if (canvasEstados) {
                    new Chart(canvasEstados, {
                        type: 'doughnut',

                        data: {
                            labels: labelsEstados,
                            datasets: [
                                {
                                    data: datosEstados,
                                    backgroundColor: [
                                        'rgba(25, 135, 84, .85)',
                                        'rgba(13, 110, 253, .85)',
                                        'rgba(255, 193, 7, .85)',
                                    ],
                                    borderWidth: 0,
                                    hoverOffset: 8,
                                },
                            ],
                        },

                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 18,
                                    },
                                },
                            },
                        },
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
