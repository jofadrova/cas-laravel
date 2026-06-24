<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioInstitucion extends Model
{
    protected $table = 'socio_institucion';
    public $timestamps = false;

    protected $fillable = [
        'id_socio',
        'papeleta',
        'carnet_mil',
        'cossmil',
        'afil_mes',
        'afil_anio',
        'anio_prom',
        'id_escalafon',
        'id_fuerza',
        'id_arma',
        'id_grado',
        'id_diplomado',
        'salario',
        'estado',
        'devolAportes',
        'devolCapitalizacion',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class,'id_socio','id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class,'id_grado','id_grado');
    }

    public function escalafon()
    {
        return $this->belongsTo(
            Escalafon::class,
            'id_escalafon'
        );
    }

    public function fuerza()
    {
        return $this->belongsTo(
            Fuerza::class,
            'id_fuerza'
        );
    }

    public function arma()
    {
        return $this->belongsTo(
            Arma::class,
            'id_arma'
        );
    }

    public function diplomado()
    {
        return $this->belongsTo(
            Diplomado::class,
            'id_diplomado'
        );
    }


}
