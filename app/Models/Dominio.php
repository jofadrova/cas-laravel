<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominios';

    public $timestamps = false;

    protected $fillable = [
        'dominio',
        'abrev',
        'Descripcion',
    ];
}
