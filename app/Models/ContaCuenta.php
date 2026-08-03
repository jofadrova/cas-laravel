<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaCuenta extends Model
{
    protected $table = 'conta_cuentas';

    protected $fillable = [
        'cuenta_padre_id', 'codigo', 'nombre', 'tipo', 'naturaleza', 'moneda',
        'nivel', 'acepta_movimientos', 'estado', 'vigente_desde', 'vigente_hasta',
        'referencia_normativa', 'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'acepta_movimientos' => 'boolean',
            'estado' => 'boolean',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cuenta_padre_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(self::class, 'cuenta_padre_id')->orderBy('codigo');
    }

    public function scopeOrdenJerarquico(Builder $query): Builder
    {
        return $query->orderBy('codigo');
    }
}
