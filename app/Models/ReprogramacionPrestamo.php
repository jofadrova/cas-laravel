<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReprogramacionPrestamo extends Model
{
    protected $table = 'reprogramaciones_prestamos';

    protected $fillable = [
        'id_solicitud',
        'cuotas_pagadas',
        'cuotas_pendientes_anterior',
        'cuotas_pendientes_nuevo',
        'periodo_anterior',
        'periodo_nuevo',
        'saldo_capital',
        'cuota_anterior',
        'cuota_nueva',
        'autorizacion',
        'observaciones',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'saldo_capital' => 'decimal:2',
        'cuota_anterior' => 'decimal:2',
        'cuota_nueva' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_solicitud', 'id_solicitud');
    }
}
