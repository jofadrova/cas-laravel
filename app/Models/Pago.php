<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [

        'monto',
        'diferencia',
        'tipo_moneda',
        'tipo_cambio',
        'fecha',
        'tipo_pago',
        'anexo',
        'estado',

    ];

    protected $casts = [

        'fecha' => 'date',
        'monto' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'tipo_cambio' => 'decimal:5'
    ];

    public function pagosCuotas()
    {
        return $this->hasMany(
            PagoCuota::class,
            'id_pago',
            'id'
        );
    }

    public function amortizacionCapital()
    {
        return $this->hasOne(AmortizacionCapital::class, 'id_pago');
    }
}
