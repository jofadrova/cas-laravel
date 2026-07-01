<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'indicators:update';

    /**
     * The console command description.
     */
    protected $description = 'Actualiza los indicadores económicos (USD y UFV)';

    /**
     * Execute the console command.
     */
    public function handle(ExchangeRateService $service): int
    {
        try {

            $rate = $service->updateToday();

            $this->info('✔ Indicadores actualizados correctamente');

            $this->table(
                ['Fecha', 'USD', 'UFV'],
                [[
                    $rate->rate_date,
                    $rate->usd_bob,
                    $rate->ufv_bob,
                ]]
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
