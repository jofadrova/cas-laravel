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

            'capital'       => $datos['monto'],
            'cuota_base' => $cuotaBase,
            'cuota' => $cronograma[0]['cuota'],
            //'cuota_base' => round($cuotaBase,2),
            //'cuota' => round($cronograma[0]['cuota'],2),
            'cronograma'    => $cronograma,
            'interesTotal'  => collect($cronograma)->sum('interes'),
            'capitalTotal'  => collect($cronograma)->sum('capital'),
            'totalPagado'   => collect($cronograma)->sum('cuota'),

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
        $saldo = $datos['monto'];
        //$saldo = round($datos['monto'],2);
        $fechaPrestamo = Carbon::parse($datos['fecha']);
        $plazo = (int)$datos['plazo'];
        $tasa = $datos['porcentaje'] / 100;
        $cronograma = [];
        $diasMes = $fechaPrestamo->daysInMonth;
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
        //$itfTotal = round(($datos['monto'] / 1000) * ($datos['itf'] ?? 0),2);

        $itfCuota = 0;
        if ($itfTotal > 0) {
            if ($plazo >= 24) {
                $itfCuota = $itfTotal / 24;
                //$itfCuota = round($itfTotal / 24,2);
            } else {
                $itfCuota = $itfTotal / $plazo;
                //$itfCuota = round($itfTotal / $plazo,2);
            }
        }

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
            //$interes = round($interesReal,2);
          //  $capital = round($capitalReal,2);
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
            | ÚLTIMA CUOTA
            |--------------------------------------------------------------------------
            */
            $cuotaMostrar = $cuotaBase;
            if ($n == $plazo) {
                $capital = $saldo;
                $cuotaMostrar = $capital + $interes;
                //$cuotaBase = $capital + $interes;
                //$cuotaBase = round($capital + $interes, 2);
            }
            $saldo -= $capitalReal;
            if ($saldo < 0) {
                $saldo = 0;
            }
            /*
            |--------------------------------------------------------------------------
            | ITF (solo primeras 24 cuotas)
            |--------------------------------------------------------------------------
            */

            $itf = 0;
            if ($plazo >= 24) {
                if ($n <= 24) {
                    $itf = $itfCuota;
                    if ($n == 24) {
                        $itf = $itfTotal - ($itfCuota * 23);
                        //$itf = round($itfTotal - ($itfCuota * 23),2);
                    }
                }
            } else {
                $itf = $itfCuota;
                if ($n == $plazo) {
                    $itf = $itfTotal - ($itfCuota * ($plazo - 1));
                    //$itf = round($itfTotal - ($itfCuota * ($plazo - 1)),2);
                }
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

            $cuotaFinal = $cuotaMostrar + $itf + $minDefensa + $papel + $cargoReposicion;

            //$cuotaFinal = $cuotaBase + $itf + $minDefensa + $papel + $cargoReposicion;

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
                /*
                'min_defensa' => round($minDefensa,2),

                'itf' => round($itf,2),

                'interes_dias' => round($papel,2),

                'reposicion' => round($cargoReposicion,2),

                'cuota_base' => round($cuotaBase,2),

                'cuota' => round($cuotaFinal,2), */

                'saldo' => $saldo,

            ];

        }
        // DEBUG
// dd($cronograma[0], $cronograma[1]);
        return $cronograma;
    }
    

}