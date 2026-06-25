<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasa extends Model
{
    protected $table = 'tasa';
    protected $primaryKey = 'id_tasa';
    public $timestamps = false;
     protected $fillable = [
        'descripcion_tasa',
        'porcentaje',
        'cod_desc',
        'tipo_moneda',
        'min_defensa',
        'itf',
        'int_penal',
        'papeleria',
        'monto_max',
        'plazo_max',
        'garante',
        'obs',
        'estado',
        'tipo_tasa'
    ];
}
