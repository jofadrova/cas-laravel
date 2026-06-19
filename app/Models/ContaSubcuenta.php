<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaSubcuenta extends Model
{
    protected $table = 'conta_subcuentas';

    protected $fillable = [
        'codigo',
        'descripcion',
        'cod_tipo',
        'estado',
        'id_socio',
    ];

    public function socio()
    {
        return $this->belongsTo(
            Socio::class,
            'id_socio'
        );
    }
}
