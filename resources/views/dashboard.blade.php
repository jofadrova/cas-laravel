<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Datos financieros
        </h2>
    </x-slot>
    <div class="row g-4 mb-4">
    <!-- USD -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-4 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">DÓLAR (USD)</div>
                        <div class="display-6 fw-bold text-success">
                            {{ number_format($exchangeRate?->usd_bob ?? 0,2) }}
                        </div>
                        @if($exchangeRate?->usd_variation)
                            <small class="{{ $exchangeRate->usd_variation >=0 ? 'text-success':'text-danger' }}">
                                <i class="bi {{ $exchangeRate->usd_variation >=0 ? 'bi-arrow-up':'bi-arrow-down' }}"></i>
                                {{ number_format(abs($exchangeRate->usd_variation),4) }}
                                ({{ number_format(abs($exchangeRate->usd_variation_percent),2) }}%)
                            </small>
                        @endif
                    </div>
                    <i class="bi bi-currency-dollar text-success" style="font-size:55px;opacity:.20"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- UFV -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">
                            UFV
                        </div>
                        <div class="display-6 fw-bold text-primary">
                            {{ number_format($exchangeRate?->ufv_bob ?? 0,4) }}
                        </div>
                    </div>
                    <i class="bi bi-graph-up-arrow text-primary" style="font-size:55px;opacity:.20"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Promedio USD -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-4 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">
                            PROMEDIO USD
                        </div>
                        <div class="display-6 fw-bold text-warning">
                            {{ number_format($monthlyAverage['usd'],2) }}
                        </div>
                    </div>
                    <i class="bi bi-bar-chart text-warning" style="font-size:55px;opacity:.20"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Promedio UFV -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-top border-4 border-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">
                            PROMEDIO UFV
                        </div>
                        <div class="display-6 fw-bold text-secondary">
                            {{ number_format($monthlyAverage['ufv'],4) }}
                        </div>
                    </div>
                    <i class="bi bi-calendar3 text-secondary" style="font-size:55px;opacity:.20"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white">
                <strong>Evolución del USD</strong>
            </div>
            <div class="card-body" style="height:350px">
                <canvas id="usdChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
    <div class="card shadow-sm border-0 rounded-4 h-100">
        <div class="card-header bg-white">
            <strong>Últimas cotizaciones</strong>
        </div>
        <div class="cotizaciones-scroll">
            <table class="table table-sm table-hover align-middle mb-0">
                <tbody>
                @foreach($history->sortByDesc('rate_date') as $item)
                    <tr>
                        <td>
                            {{ $item->rate_date->format('d/m') }}
                        </td>

                        <td class="text-end fw-bold">
                            {{ number_format($item->usd_bob,2) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</x-app-layout>
<script>
document.addEventListener('DOMContentLoaded', () => {

    initDashboard(
        @json($history->pluck('rate_date')->map->format('d/m')),
        @json($history->pluck('usd_bob'))
    );

});
</script>
