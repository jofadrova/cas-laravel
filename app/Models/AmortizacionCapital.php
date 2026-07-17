<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmortizacionCapital extends Model
{
    protected $table = 'amortizaciones_capital';

    protected $fillable = [
        'id_solicitud',
        'id_pago',
        'tipo_recalculo',
        'monto_efectivo',
        'monto_capital',
        'tipo_cambio',
        'saldo_anterior',
        'saldo_nuevo',
        'cuota_anterior',
        'cuota_nueva',
        'periodo_anterior',
        'periodo_nuevo',
        'autorizacion',
        'observaciones',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'monto_efectivo' => 'decimal:2',
        'monto_capital' => 'decimal:2',
        'tipo_cambio' => 'decimal:5',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
        'cuota_anterior' => 'decimal:2',
        'cuota_nueva' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_solicitud', 'id_solicitud');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago');
    }
}
