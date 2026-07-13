<?php

namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use App\Models\Tasa;
use App\Models\CuotaSolicitud;
use Carbon\Carbon;
use App\Models\HistorialGarante;
use Illuminate\Support\Facades\Auth;

class PrestamoService
{
    public function consolidar(array $datos): Prestamo
    {
        return DB::transaction(function () use ($datos) {

            $tasa = Tasa::findOrFail($datos['tipo_prestamo']);

            $this->validarDatos($datos, $tasa);

            $prestamo = $this->guardarSolicitud(null,$datos, $tasa);
            $this->guardarCuotas($prestamo, json_decode($datos['cronograma'], true));
            return $prestamo;
        });
    }

    private function guardarSolicitud(?Prestamo $prestamo, array $datos, Tasa $tasa): Prestamo
    {
        $prestamo ??= new Prestamo();
        if (!$prestamo->exists) {
             $prestamo->nro_solicitud =(Prestamo::max('nro_solicitud') ?? 0) + 1;
             $prestamo->editable = true;
        }

        if ($prestamo->exists) {
            $prestamo->fecha_actualizacion = now();
        }

        $prestamo->fill([
             
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
            'fecha_deposito' => $datos['fechaPrestamo'],
            'tipo_cambio' => $datos['tipo_cambio'],

        ]);
        $prestamo->save();
        return $prestamo;


    }

    private function guardarCuotas(Prestamo $prestamo,array $cronograma): void {
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

    private function validarDatos(array $datos, Tasa $tasa): void
    {
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
    }

    public function actualizar(Prestamo $prestamo, array $datos): Prestamo
    {
        return DB::transaction(function () use ($prestamo, $datos) {

            $tasa = Tasa::findOrFail($datos['tipo_prestamo']);
            $this->validarDatos($datos, $tasa);
            $prestamo = $this->guardarSolicitud($prestamo,$datos,$tasa);

            CuotaSolicitud::where(
                'id_solicitud',
                $prestamo->id_solicitud
            )->delete();

            $this->guardarCuotas(
                $prestamo,
                json_decode($datos['cronograma'], true)
            );

            return $prestamo;

        });
    }

    public function actualizarGarantes(Prestamo $prestamo, array $datos): void
    {
        DB::transaction(function () use ($prestamo, $datos) {

            $garante1Old = $prestamo->id_garante1;
            $garante2Old = $prestamo->id_garante2;

            $garante1New = $datos['id_garante1'] ?? $garante1Old;
            $garante2New = $datos['id_garante2'] ?? $garante2Old;

            $prestamo->update([
                'id_garante1'       => $garante1New,
                'id_garante2'       => $garante2New,
                'fecha_actualizacion' => now(),
            ]);

            if ($garante1Old != $garante1New && $garante2Old != $garante2New) {
                $tipo = 'AMBOS';
            } elseif ($garante1Old != $garante1New) {
                $tipo = 'GARANTE1';
            } else {
                $tipo = 'GARANTE2';
            }

            HistorialGarante::create([
                'id_solicitud'  => $prestamo->id_solicitud,

                'garante1_old'  => $garante1Old,
                'garante2_old'  => $garante2Old,

                'garante1_new'  => $garante1New,
                'garante2_new'  => $garante2New,

                'id_usuario'    => Auth::id(),

                'tipo_cambio'   => $tipo,

                'fecha'         => now(),

                'observaciones' => $datos['observaciones'],
            ]);

        });
    }
}
