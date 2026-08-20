<?php

namespace App\Services\ProcesamientoMensual;

use App\Models\EnvioMensual;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnvioPrestamosTxtService
{
    private const ESTADOS_PRESTAMO = ['AC', 'DI', 'PR'];

    private const ESTADOS_SOCIO = ['IN', 'SO', 'AC', 'BA', 'PR', 'GA'];

    private const MESES = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    /**
     * @return array{contenido:string,nombre:string,cantidad:int,monto_total:string,hash_sha256:string}
     */
    public function generar(EnvioMensual $envio): array
    {
        $registros = $this->consulta($envio)->get();

        if ($registros->isEmpty()) {
            throw ValidationException::withMessages([
                'prestamos' => "No existen cuotas pendientes para {$envio->periodo} con las reglas de envío vigentes.",
            ]);
        }

        $this->validar($registros);

        $lineas = [];
        $montoTotalCentavos = 0;

        foreach ($registros as $registro) {
            $monto = (int) round(
                (float) $registro->cuota_fija
                * ($registro->tipo_moneda === 'B'
                    ? 1
                    : (float) $registro->tipo_cambio)
                * 100
            );

            $montoTotalCentavos += $monto;
            $lineas[] = implode('*', [
                $registro->id_fuerza,
                $registro->papeleta,
                '171',
                '0'.$registro->tipo_prestamo,
                '1',
                'B',
                number_format($monto / 100, 2, '.', ''),
                '1',
            ]);
        }

        // El archivo oficial usa CRLF, no contiene BOM ni salto final.
        $contenido = implode("\r\n", $lineas);

        return [
            'contenido' => $contenido,
            'nombre' => sprintf(
                'DESCUENTOS_CAS_RL_%s_%d_PRESTAMOS_FINAL.txt',
                self::MESES[$envio->mes],
                $envio->gestion
            ),
            'cantidad' => count($lineas),
            'monto_total' => number_format($montoTotalCentavos / 100, 2, '.', ''),
            'hash_sha256' => hash('sha256', $contenido),
        ];
    }

    /**
     * @return array{cantidad:int,tipos_cambio_invalidos:int,datos_invalidos:int}
     */
    public function resumen(EnvioMensual $envio): array
    {
        $base = $this->consulta($envio);

        return [
            'cantidad' => (clone $base)->count(),
            'tipos_cambio_invalidos' => (clone $base)
                ->where('ta.tipo_moneda', '<>', 'B')
                ->where(function (Builder $query): void {
                    $query->whereNull('s.tipo_cambio')
                        ->orWhere('s.tipo_cambio', '<=', 0);
                })
                ->count(),
            'datos_invalidos' => (clone $base)
                ->where(function (Builder $query): void {
                    $query->whereNull('si.id_fuerza')
                        ->orWhere('si.id_fuerza', '<=', 0)
                        ->orWhereNull('si.papeleta')
                        ->orWhereRaw("TRIM(si.papeleta) = ''")
                        ->orWhereRaw("si.papeleta LIKE '%*%'");
                })
                ->count(),
        ];
    }

    private function consulta(EnvioMensual $envio): Builder
    {
        return DB::table('solicitudes as s')
            ->join('cuotas_solicitud as cs', 'cs.id_solicitud', '=', 's.id_solicitud')
            ->join('socios as so', 'so.id', '=', 's.ide_per')
            ->join('socio_institucion as si', 'si.id_socio', '=', 'so.id')
            ->join('tasa as ta', 'ta.id_tasa', '=', 's.tipo_prestamo')
            ->whereIn('s.estado', self::ESTADOS_PRESTAMO)
            ->whereIn('so.estado', self::ESTADOS_SOCIO)
            ->where('so.mindef', '<>', 'BA')
            ->where('cs.estado', 'PE')
            ->where('cs.gestion', $envio->gestion)
            ->where('cs.mes', $envio->mes)
            ->select([
                's.id_solicitud',
                's.tipo_prestamo',
                's.tipo_cambio',
                'ta.tipo_moneda',
                'cs.id as cuota_id',
                'cs.cuota_fija',
                'si.id_fuerza',
                'si.papeleta',
            ])
            ->orderBy('s.tipo_prestamo')
            ->orderBy('s.id_solicitud')
            ->orderBy('cs.id');
    }

    private function validar(Collection $registros): void
    {
        $sinTipoCambio = $registros->filter(fn (object $registro): bool =>
            $registro->tipo_moneda !== 'B'
            && ($registro->tipo_cambio === null || (float) $registro->tipo_cambio <= 0)
        );

        if ($sinTipoCambio->isNotEmpty()) {
            $ejemplos = $sinTipoCambio->pluck('id_solicitud')->unique()->take(10)->implode(', ');

            throw ValidationException::withMessages([
                'prestamos' => $sinTipoCambio->count().' cuota(s) corresponden a préstamos en dólares sin tipo de cambio válido guardado. '
                    .'Préstamos de ejemplo: '.$ejemplos.'. No se generó un archivo con montos incorrectos.',
            ]);
        }

        $datosInvalidos = $registros->filter(fn (object $registro): bool =>
            empty($registro->id_fuerza)
            || trim((string) $registro->papeleta) === ''
            || str_contains((string) $registro->papeleta, '*')
        );

        if ($datosInvalidos->isNotEmpty()) {
            $ejemplos = $datosInvalidos->pluck('id_solicitud')->unique()->take(10)->implode(', ');

            throw ValidationException::withMessages([
                'prestamos' => $datosInvalidos->count().' cuota(s) tienen fuerza o papeleta inválida. '
                    .'Préstamos de ejemplo: '.$ejemplos.'.',
            ]);
        }
    }
}
