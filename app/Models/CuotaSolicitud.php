<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuotaSolicitud extends Model
{
    protected $table = 'cuotas_solicitud';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_solicitud',
        'nro_cuota',
        'mes',
        'gestion',
        'cuota_fija',
        'amortizacion_cap',
        'interes',
        'min_defensa',
        'itf',
        'papel',
        'capital_amortizado',
        'saldo',
        'estado',
        'comision_mindef',
        'rep_formulario',
    ];

    public function solicitud()
    {
        return $this->belongsTo(
            Prestamo::class,
            'id_solicitud',
            'id_solicitud'
        );
    }

    public function pagos()
    {
        return $this->hasMany(
            PagoCuota::class,
            'id_cuota_solicitud',
            'id'
        );
    }
}
