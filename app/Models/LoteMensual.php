<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteMensual extends Model
{
    public const ESTADO_BORRADOR = 'BORRADOR';

    public const ESTADO_CARGADO = 'CARGADO';

    public const ESTADO_PROCESADO = 'PROCESADO';

    public const ESTADO_CERRADO = 'CERRADO';

    public const ESTADO_ANULADO = 'ANULADO';

    public const ESTADOS = [
        self::ESTADO_BORRADOR,
        self::ESTADO_CARGADO,
        self::ESTADO_PROCESADO,
        self::ESTADO_CERRADO,
        self::ESTADO_ANULADO,
    ];

    public const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    protected $table = 'lotes_mensuales';

    protected $fillable = [
        'mes',
        'gestion',
        'tipo_cambio',
        'fecha_recepcion',
        'estado',
        'observaciones',
        'creado_por',
        'cerrado_por',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'mes' => 'integer',
            'gestion' => 'integer',
            'tipo_cambio' => 'decimal:5',
            'fecha_recepcion' => 'date',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function getNombreMesAttribute(): string
    {
        return self::MESES[$this->mes] ?? 'Mes no válido';
    }

    public function getPeriodoAttribute(): string
    {
        return "{$this->nombre_mes} {$this->gestion}";
    }

    public function getCodigoPeriodoAttribute(): string
    {
        return sprintf('%04d-%02d', $this->gestion, $this->mes);
    }

    public function getClaseEstadoAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'bg-secondary',
            self::ESTADO_CARGADO => 'bg-primary',
            self::ESTADO_PROCESADO => 'bg-dark',
            self::ESTADO_CERRADO => 'bg-success',
            self::ESTADO_ANULADO => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function cerrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function scopeDelPeriodo(
        Builder $query,
        int $mes,
        int $gestion
    ): Builder {
        return $query
            ->where('mes', $mes)
            ->where('gestion', $gestion);
    }

    public function estaCerrado(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }

    public function estaAnulado(): bool
    {
        return $this->estado === self::ESTADO_ANULADO;
    }

    public function puedeEditar(): bool
    {
        return ! in_array(
            $this->estado,
            [
                self::ESTADO_CARGADO,
                self::ESTADO_PROCESADO,
                self::ESTADO_CERRADO,
                self::ESTADO_ANULADO,
            ],
            true
        );
    }
}
