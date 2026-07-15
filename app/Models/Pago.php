<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [

        'monto',
        'tipo_moneda',
        'fecha',
        'tipo_pago',
        'anexo',
        'estado',

    ];

    protected $casts = [

        'fecha' => 'date',
        'monto' => 'decimal:2',

    ];

    public function pagosCuotas()
    {
        return $this->hasMany(
            PagoCuota::class,
            'id_pago',
            'id'
        );
    }
}
