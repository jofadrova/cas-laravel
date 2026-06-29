<?php

namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use App\Models\Tasa;
use App\Models\CuotaSolicitud;
use Carbon\Carbon;

class PrestamoService
{
    public function consolidar(array $datos): Prestamo
    {
        return DB::transaction(function () use ($datos) {

            $tasa = Tasa::findOrFail($datos['tipo_prestamo']);

            // Validaciones de negocio
            if ($datos['monto'] > $tasa->monto_max) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monto' => 'El monto solicitado supera el máximo permitido.'
                ]);
            }

            if ($datos['plazo'] > $tasa->plazo_max) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'plazo' => 'El plazo solicitado supera el plazo máximo permitido.'
                ]);
            }
            $prestamo = $this->guardarSolicitud($datos, $tasa);
            $this->guardarCuotas($prestamo, json_decode($datos['cronograma'], true));
           


            return $prestamo;

        });
    }
    private function guardarSolicitud(array $datos, Tasa $tasa): Prestamo
    {
        $nroSolicitud = (Prestamo::max('nro_solicitud') ?? 0) + 1;
        $prestamo = new Prestamo();

        $prestamo->fill([
             'nro_solicitud' => $nroSolicitud,
            'ide_per'        => $datos['id_socio'],
            'tipo_prestamo'  => $tasa->id_tasa,

            'id_garante1' => !empty($datos['id_garante1']) ? $datos['id_garante1'] : 0,
            'id_garante2' => !empty($datos['id_garante2']) ? $datos['id_garante2'] : 0,

            'monto'          => $datos['monto'],
            'saldo_actual'   => $datos['monto'],

            'periodo'        => $datos['plazo'],
            'ultima_cuota'   => 0,

            'motivo'         => $datos['motivo'],
            'asiento'        => $datos['asiento'],

            'interes'        => $tasa->porcentaje,
            'min_defensa'    => $tasa->min_defensa,
            'itf'            => $tasa->itf,

            'monto_mora'     => 0,

            'estado'         => 'AC',

            'gadm'           => 0,
            'periodo_gadm'   => 0,

        ]);
        $prestamo->save();
        return $prestamo;

       
    }

    private function guardarCuotas(
    Prestamo $prestamo,
    array $cronograma
): void {

    foreach ($cronograma as $fila) {

        $fecha = Carbon::parse($fila['fecha']);

        $mes = $fecha->month;
        $gestion = $fecha->year;

        CuotaSolicitud::create([

            'id_solicitud'       => $prestamo->id_solicitud,
            'nro_cuota'          => $fila['numero'],
            'mes' => $mes,
            'gestion' => $gestion,
            'cuota_fija'         => round($fila['cuota'], 2),
            'amortizacion_cap'   => round($fila['capital'], 2),
            'interes'            => round($fila['interes'], 2),
            'min_defensa'        => round($fila['min_defensa'], 2),
            'itf'                => round($fila['itf'], 2),
            'papel'              => round($fila['reposicion'], 2),
            // En el Legacy este campo guarda el capital amortizado
            'capital_amortizado' => round($fila['capital'], 2),
            'saldo'              => round($fila['saldo'], 2),
            'estado'             => 'PE',
            'comision_mindef'    => null,
            'rep_formulario'     => null,
        ]);
    }
}
}
