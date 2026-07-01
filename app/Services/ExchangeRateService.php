<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    private string $url = 'https://api.factura.bo/ExchangeRate';

    public function updateToday(): ExchangeRate
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->get($this->url);

        if (!$response->successful()) {
            throw new \Exception('No se pudo obtener la cotización.');
        }

        $datos = $response->json('datos');

        $rateDate = Carbon::parse($datos['fecha_actualizacion'])->toDateString();

        // Última cotización anterior
        $previous = ExchangeRate::where('rate_date', '<', $rateDate)
            ->orderByDesc('rate_date')
            ->first();

        $usdVariation = null;
        $usdVariationPercent = null;

        $ufvVariation = null;
        $ufvVariationPercent = null;

        if ($previous) {

            // USD
            $usdVariation = $datos['usd_bob'] - $previous->usd_bob;

            if ($previous->usd_bob != 0) {
                $usdVariationPercent = ($usdVariation / $previous->usd_bob) * 100;
            }

            // UFV
            $ufvVariation = $datos['ufv_bob'] - $previous->ufv_bob;

            if ($previous->ufv_bob != 0) {
                $ufvVariationPercent = ($ufvVariation / $previous->ufv_bob) * 100;
            }
        }

        return ExchangeRate::updateOrCreate(
            [
                'rate_date' => $rateDate,
            ],
            [
                'usd_bob' => $datos['usd_bob'],
                'usd_variation' => $usdVariation,
                'usd_variation_percent' => $usdVariationPercent,

                'ufv_bob' => $datos['ufv_bob'],
                'ufv_variation' => $ufvVariation,
                'ufv_variation_percent' => $ufvVariationPercent,

                'source' => $datos['fuente'],
                'source_url' => $datos['url_fuente'],
                'api_timestamp' => Carbon::parse($datos['fecha_actualizacion']),
            ]
        );
    }

    public function today(): ?ExchangeRate
    {
        return ExchangeRate::whereDate('rate_date', today())->first();
    }

    public function monthlyAverage(): array
    {
        $inicio = now()->startOfMonth();

        return [
            'usd' => round(
                ExchangeRate::where('rate_date', '>=', $inicio)->avg('usd_bob'),
                4
            ),
            'ufv' => round(
                ExchangeRate::where('rate_date', '>=', $inicio)->avg('ufv_bob'),
                4
            ),
        ];
    }

    public function getLatest(): ?ExchangeRate
    {
        return ExchangeRate::orderByDesc('rate_date')->first();
    }

    public function getMonthlyAverages(): array
    {
        $latest = $this->getLatest();

        if (!$latest) {
            return [
                'usd' => 0,
                'ufv' => 0,
            ];
        }

        return [
            'usd' => round(
                ExchangeRate::whereYear('rate_date', $latest->rate_date->year)
                    ->whereMonth('rate_date', $latest->rate_date->month)
                    ->avg('usd_bob') ?? 0,
                4
            ),

            'ufv' => round(
                ExchangeRate::whereYear('rate_date', $latest->rate_date->year)
                    ->whereMonth('rate_date', $latest->rate_date->month)
                    ->avg('ufv_bob') ?? 0,
                4
            ),
        ];
    }

    public function getLastDays(int $days = 30)
    {
        return ExchangeRate::orderByDesc('rate_date')
            ->take($days)
            ->get()
            ->sortBy('rate_date')
            ->values();
    }

    public function getHistory(int $days = 30)
    {
        return ExchangeRate::query()
            ->orderByDesc('rate_date')
            ->limit($days)
            ->get()
            ->sortBy('rate_date')
            ->values();
    }
}
