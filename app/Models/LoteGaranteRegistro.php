<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteGaranteRegistro extends Model
{
    public const CONCILIACION_SIN_COMPARAR = 'SIN_COMPARAR';
    public const CONCILIACION_COINCIDE = 'COINCIDE';
    public const CONCILIACION_FALTA = 'FALTA';
    public const CONCILIACION_DEMASIA = 'DEMASIA';
    public const CONCILIACION_SIN_DESCUENTO = 'SIN_DESCUENTO_EXCEL';
    public const CONCILIACION_OBSERVADO = 'OBSERVADO';

    public const APLICACION_IMPORTADO = 'IMPORTADO';
    public const APLICACION_PENDIENTE = 'PENDIENTE';
    public const APLICACION_LISTO = 'LISTO_PARA_APLICAR';
    public const APLICACION_OBSERVADO = 'OBSERVADO';
    public const APLICACION_APLICADO = 'APLICADO';
    public const APLICACION_ANULADO = 'ANULADO';

    protected $table = 'lote_garante_registros';

    protected $fillable = [
        'lote_mensual_id',
        'lote_archivo_id',
        'fila_origen',
        'codigo_titular',
        'nombre_titular',
        'tipo_garante',
        'codigo_garante',
        'nombre_garante',
        'monto_bs',
        'observacion_excel',
        'id_socio_titular',
        'id_socio_garante',
        'id_solicitud',
        'id_cuota_solicitud',
        'tipo_prestamo',
        'factor_conversion',
        'monto_aplicable',
        'monto_acumulado',
        'saldo_pendiente',
        'estado_conciliacion',
        'estado_aplicacion',
        'observacion_sistema',
        'pago_id',
        'procesado_por',
        'fecha_procesamiento',
    ];

    protected function casts(): array
    {
        return [
            'fila_origen' => 'integer',
            'monto_bs' => 'decimal:6',
            'factor_conversion' => 'decimal:5',
            'monto_aplicable' => 'decimal:6',
            'monto_acumulado' => 'decimal:6',
            'saldo_pendiente' => 'decimal:6',
            'fecha_procesamiento' => 'datetime',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMensual::class, 'lote_mensual_id');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(LoteArchivo::class, 'lote_archivo_id');
    }

    public function conciliaciones(): HasMany
    {
        return $this->hasMany(
            LotePrestamoConciliacion::class,
            'lote_garante_registro_id'
        );
    }

    public function getClaseAplicacionAttribute(): string
    {
        return match ($this->estado_aplicacion) {
            self::APLICACION_LISTO => 'bg-success',
            self::APLICACION_PENDIENTE => 'bg-warning text-dark',
            self::APLICACION_OBSERVADO => 'bg-danger',
            self::APLICACION_APLICADO => 'bg-primary',
            self::APLICACION_ANULADO => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}
