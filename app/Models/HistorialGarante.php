<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialGarante extends Model
{
    protected $table = 'historial_garantes';

    protected $fillable = [

        'id_solicitud',
        'garante1_old',
        'garante2_old',
        'garante1_new',
        'garante2_new',
        'id_usuario',
        'tipo_cambio',
        'fecha',
        'observaciones'
    ];

    public $timestamps = true;

    public function prestamo()
    {
        return $this->belongsTo(
            Prestamo::class,
            'id_solicitud'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(User::class,'id_usuario');
    }
    public function garante1Old()
    {
        return $this->belongsTo(
            Socio::class,
            'garante1_old'
        );
    }

    public function garante2Old()
    {
        return $this->belongsTo(
            Socio::class,
            'garante2_old'
        );
    }

    public function garante1New()
    {
        return $this->belongsTo(
            Socio::class,
            'garante1_new'
        );
    }

    public function garante2New()
    {
        return $this->belongsTo(
            Socio::class,
            'garante2_new'
        );
    }

   
}
