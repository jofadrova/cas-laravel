<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EnvioMensual extends Model
{
    public const ESTADO_BORRADOR = 'BORRADOR';
    public const ESTADO_PREPARANDO = 'PREPARANDO';
    public const ESTADO_VALIDADO = 'VALIDADO';
    public const ESTADO_ENVIADO = 'ENVIADO';
    public const ESTADO_RECIBIDO = 'RECIBIDO';
    public const ESTADO_CERRADO = 'CERRADO';
    public const ESTADO_ANULADO = 'ANULADO';

    public const ESTADOS = [
        self::ESTADO_BORRADOR,
        self::ESTADO_PREPARANDO,
        self::ESTADO_VALIDADO,
        self::ESTADO_ENVIADO,
        self::ESTADO_RECIBIDO,
        self::ESTADO_CERRADO,
        self::ESTADO_ANULADO,
    ];

    protected $table = 'envios_mensuales';

    protected $fillable = [
        'mes', 'gestion', 'destinatario', 'fecha_envio',
        'estado', 'observaciones',
        'creado_por', 'cerrado_por', 'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'mes' => 'integer',
            'gestion' => 'integer',
            'fecha_envio' => 'date',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function loteMensual(): HasOne
    {
        return $this->hasOne(LoteMensual::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(EnvioMensualArchivo::class);
    }

    public function archivoPrestamos(): HasOne
    {
        return $this->hasOne(EnvioMensualArchivo::class)
            ->where('tipo', EnvioMensualArchivo::TIPO_PRESTAMOS);
    }

    public function archivoGarantes(): HasOne
    {
        return $this->hasOne(EnvioMensualArchivo::class)
            ->where('tipo', EnvioMensualArchivo::TIPO_GARANTES_ORIGEN);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function getCodigoAttribute(): string
    {
        return 'ENV-' . str_replace('-', '', $this->codigo_periodo);
    }

    public function getNombreMesAttribute(): string
    {
        return LoteMensual::MESES[$this->mes] ?? 'Mes no válido';
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
            self::ESTADO_PREPARANDO => 'bg-primary',
            self::ESTADO_VALIDADO => 'bg-info text-dark',
            self::ESTADO_ENVIADO => 'bg-primary',
            self::ESTADO_RECIBIDO => 'bg-warning text-dark',
            self::ESTADO_CERRADO => 'bg-success',
            self::ESTADO_ANULADO => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
