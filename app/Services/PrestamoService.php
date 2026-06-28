<?php

namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use App\Models\Tasa;

class PrestamoService
{
    public function consolidar(array $datos): Prestamo
    {
        return DB::transaction(function () use ($datos) {

            $prestamo = $this->guardarSolicitud($datos);

            return $prestamo;

        });
    }

    private function guardarSolicitud(array $datos, Tasa $tasa): Prestamo
    {
        $prestamo = new Prestamo();

        $prestamo->fill([

            'ide_per'        => $datos['id_socio'],
            'tipo_prestamo'  => $tasa->id_tasa,

            'id_garante1'    => $datos['id_garante1'] ?? null,
            'id_garante2'    => $datos['id_garante2'] ?? null,

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

         // 1. Inserta el registro en la BD
        $prestamo->save();

        // 2. Ahora Laravel ya conoce el id_solicitud generado
        $prestamo->nro_solicitud = $prestamo->id_solicitud;

        // 3. Actualiza únicamente ese campo
        $prestamo->save();

        return $prestamo;
    }
}
