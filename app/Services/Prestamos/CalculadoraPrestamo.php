<?php

namespace App\Services\Prestamos;

use Carbon\Carbon;

class CalculadoraPrestamo
{
    /**
     * Simula completamente un préstamo.
     *
     * Basado en el algoritmo del sistema Legacy.
     * Toda modificación debe compararse contra producción.
     */
    public function simular(array $datos): array
    {
        $cuotaBase = $this->calcularCuotaBase(
            $datos['monto'],
            $datos['porcentaje'],
            $datos['plazo']
        );

        $cronograma = $this->generarCronograma(
            $datos,
            $cuotaBase
        );

        return [
            'capital' => $datos['monto'],
            'cuota_base' => $cuotaBase,
            'cuota' => $cronograma[0]['cuota'],
            'cronograma' => $cronograma,
            // Los totales se acumulan sin redondear cada componente, igual que calcula.php.
            'interesTotal' => $this->redondear(array_sum(array_column($cronograma, 'interes'))),
            'capitalTotal' => $this->redondear(array_sum(array_column($cronograma, 'capital'))),
            'itfTotal' => $this->redondear(array_sum(array_column($cronograma, 'itf'))),
            'interesDiasTotal' => $this->redondear(array_sum(array_column($cronograma, 'interes_dias'))),
            'reposicionTotal' => $this->redondear(array_sum(array_column($cronograma, 'reposicion'))),
            'totalPagado' => $this->redondear(array_sum(array_column($cronograma, 'cuota_legacy'))),
        ];
    }

    /**
     * Cuota francesa.
     * Fórmula extraída del Legacy.
     */
    protected function calcularCuotaBase(float $monto, float $tasa, int $plazo): float
    {
        $i = $tasa / 100;

        $factor = pow(1 + $i, $plazo);

        return ($i * $factor * $monto) / ($factor - 1);
    }

    /**
     * Cronograma base.
     */
    protected function generarCronograma(array $datos, float $cuotaBase): array
    {
        $saldo = (float) $datos['monto'];
        $fechaPrestamo = Carbon::parse($datos['fecha']);
        $plazo = (int)$datos['plazo'];
        $tasa = $datos['porcentaje'] / 100;
        $cronograma = [];
        // El legacy consideraba febrero siempre de 28 días, incluso en años bisiestos.
        $diasMes = $fechaPrestamo->month === 2 ? 28 : $fechaPrestamo->daysInMonth;
        $fecha2 = $diasMes - $fechaPrestamo->day;
        /*
        |--------------------------------------------------------------------------
        | MIN. DEFENSA
        |--------------------------------------------------------------------------
        */

        //$minDefensa = round(($cuotaBase * ($datos['min_defensa'] ?? 0)) / 100,2);
        $minDefensa = ($cuotaBase * ($datos['min_defensa'] ?? 0)) / 100;

        /*
        |--------------------------------------------------------------------------
        | ITF
        |--------------------------------------------------------------------------
        */

        $itfTotal = ($datos['monto'] / 1000) * ($datos['itf'] ?? 0);
        $periodoItf = $plazo >= 24 && (int) ($datos['tipo'] ?? 0) !== 8 ? 24 : $plazo;
        $itfAcumulado = 0;

        /*
        |--------------------------------------------------------------------------
        | INTERES DIAS
        |--------------------------------------------------------------------------
        */
        $interesDias = 0;
        $papel = 0;

        /*
        |--------------------------------------------------------------------------
        | REPOSICION
        |--------------------------------------------------------------------------
        */

        $reposicion = ($datos['tipo'] == 2) ? 3.00 : 0.43;       

        for ($n = 1; $n <= $plazo; $n++) {
            /*
            |--------------------------------------------------------------------------
            | INTERÉS Y CAPITAL
            |--------------------------------------------------------------------------
            */
            $interesReal = $saldo * $tasa;

            $capitalReal = $cuotaBase - $interesReal;

            $interes = $interesReal;
            $capital = $capitalReal;

            /*
            |--------------------------------------------------------------------------
            | INTERÉS POR DÍAS (Legacy)
            |--------------------------------------------------------------------------
            */

            if ($n == 1) {
               
                $auxi = $interesReal / $diasMes;
                $interesDias = ($fecha2 * $auxi);
                //$interesDias = round(($fecha2 * $auxi), 2);
            }

            if ($n <= 2 && $plazo > 1) {
                $papel = $interesDias / 2;
               // $papel = round($interesDias / 2, 2);
            } else {
                $papel = 0;
            }           
            /*
            |--------------------------------------------------------------------------
            | ITF (solo primeras 24 cuotas)
            |--------------------------------------------------------------------------
            */

            $itf = 0;
            if ($n <= $periodoItf) {
                $itf = $this->redondear($itfTotal / $periodoItf);
                if ($n === $periodoItf) {
                    $itf = $this->redondear($itfTotal) - $itfAcumulado;
                }
            }

            if ((int) ($datos['tipo'] ?? 0) === 8 && ($datos['periodo_gadm'] ?? 0) > 0 && $n <= $datos['periodo_gadm']) {
                $itf = $this->redondear(($datos['gadm'] ?? 0) / $datos['periodo_gadm']);
            }

            /*
            |--------------------------------------------------------------------------
            | REPOSICIÓN
            |--------------------------------------------------------------------------
            */

            $cargoReposicion = ($n == 1)
                ? $reposicion
                : 0;

            /*
            |--------------------------------------------------------------------------
            | CUOTA FINAL
            |--------------------------------------------------------------------------
            */

            $cuotaFinal = $this->redondear($cuotaBase + $itf + $minDefensa + $papel + $cargoReposicion);
            $saldo -= $capitalReal;
            $itfAcumulado = $this->redondear($itfAcumulado + $itf);

            $cronograma[] = [

                'numero' => $n,

                'fecha' => $fechaPrestamo
                    ->copy()
                    ->addMonths($n)
                    ->format('Y-m-d'),

                'capital' => $capital,

                'interes' => $interes,

                'min_defensa' => $minDefensa,

                'itf' => $itf,

                'interes_dias' => $papel,

                'reposicion' => $cargoReposicion,

                'cuota_base' => $cuotaBase,

                'cuota' => $cuotaFinal,

                // Legacy acumula el importe sin redondear antes de mostrar el total.
                'cuota_legacy' => $cuotaBase + $itf + $minDefensa + $papel + $cargoReposicion,

                'saldo' => abs($saldo),

            ];

        }
        // DEBUG
// dd($cronograma[0], $cronograma[1]);
        return $cronograma;
    }

    /** Equivalente a redondeado($numero, 2) del sistema legacy. */
    private function redondear(float $numero, int $decimales = 2): float
    {
        return round($numero, $decimales, PHP_ROUND_HALF_UP);
    }

}
