<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Residencia extends Model
{
    protected $table = 'residencias';
    public $timestamps = false;

    protected $fillable = [
        'id_socio',
        'departamento',
        'ciudad',
        'zona',
        'calle',
        'nro',
        'telefono',
        'estado',
        'correo',
        'formularioSolicitud',
        'afiliacionAfcoop',
        'fotocopiaCarnet',
        'resolucion',
    ];

    public function socio()
    {
        return $this->belongsTo(
            Socio::class,
            'id_socio',
            'id'
        );
    }
}
