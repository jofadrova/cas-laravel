<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoCuota extends Model
{
    protected $table = 'pagos_cuotas';

    protected $fillable = [

        'id_cuota_solicitud',
        'id_pago',
        'nro_cuota',
        'monto',
        'fecha',
        'estado',

    ];

    protected $casts = [

        'fecha' => 'date',
        'monto' => 'decimal:2',

    ];

    public function pago()
    {
        return $this->belongsTo(
            Pago::class,
            'id_pago',
            'id'
        );
    }

    public function cuotaSolicitud()
    {
        return $this->belongsTo(
            CuotaSolicitud::class,
            'id_cuota_solicitud',
            'id'
        );
    }
}
