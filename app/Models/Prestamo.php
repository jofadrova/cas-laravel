<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Prestamo extends Model
{
    protected $table = 'solicitudes';
    protected $primaryKey = 'idsolicitud';
    public $timestamps = false;
    protected $fillable = [
        'nro_solicitud',
        'ide_per',
        'tipo_prestamo',
        'id_garante1',
        'id_garante2',
        'monto',
        'monto_mora',
        'periodo',
        'saldo_actual',
        'ultima_cuota',
        'motivo',
        'asiento',
        'interes',
        'min_defensa',
        'itf',
        'estado',
        'gadm',
        'periodo_gadm',
    ];

    public function socio()
    {
        return $this->belongsTo(
            Socio::class,
            'ide_per',
            'id'
        );
    }

    public function tipo()
    {
        return $this->belongsTo(
            Tasa::class,
            'tipo_prestamo',
            'id_tasa'
        );
    }

    public function garante1()
    {
        return $this->belongsTo(
            Socio::class,
            'id_garante1',
            'id'
        );
    }
    public function garante2()
    {
        return $this->belongsTo(
            Socio::class,
            'id_garante2',
            'id'
        );
    }


}
