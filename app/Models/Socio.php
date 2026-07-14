<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table = 'socios';
    public $timestamps = false;
    protected $fillable = [
        'nombres',
        'paterno',
        'materno',
        'nro_doc',
        'expedido',
        'sexo',
        'fecha_nac',
        'estado_civil',
        'foto',
        'estado',
        'num_correlativo',
        'estado_kardex',
        'mindef',
        'id_socio_anterior',
        'id_socio_origen',
        'es_revinculacion',
        'cantidad_revinculaciones',
        'fecha_revinculacion',
        'usuario_revinculacion',
        'observacion_revinculacion',
        'vinculacion_actual'
    ];
    //
    public function residencia()
    {
        return $this->hasOne(
            Residencia::class,
            'id_socio',
            'id'
        );
    }

    public function institucion()
    {
        return $this->hasOne(
            SocioInstitucion::class,
            'id_socio',
            'id'
        );
    }

    public function dependientes()
    {
        return $this->hasMany(SocioDependiente::class, 'id_socio');
    }
}
