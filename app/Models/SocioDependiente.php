<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioDependiente extends Model
{
    protected $table = 'socio_dependientes';

    protected $fillable = [

        'id_socio',
        'nombres',
        'paterno',
        'materno',
        'ci',
        'exp',
        'parentesco',
        'porcentaje',
        'estado'

    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'id_socio');
    }

    public function parentescoDominio()
    {
        return $this->belongsTo(
            Dominio::class,
            'parentesco',
            'abrev'
        )->where('dominio', 'PARENTESCO');
    }
}