<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotePrestamoConciliacion extends Model
{
    public const CONCEPTO_CUOTA = 'CUOTA_PRESTAMO';
    public const CONCEPTO_GARANTE = 'DESCUENTO_GARANTE';

    public const COINCIDE = 'COINCIDE';
    public const FALTA = 'FALTA';
    public const DEMASIA = 'DEMASIA';
    public const SOCIO_NO_ENCONTRADO = 'SOCIO_NO_ENCONTRADO';
    public const SIN_CUOTA = 'SIN_CUOTA';
    public const TIPO_NO_CLASIFICADO = 'TIPO_NO_CLASIFICADO';

    public const CLASIFICACIONES = [
        self::COINCIDE,
        self::FALTA,
        self::DEMASIA,
        self::SOCIO_NO_ENCONTRADO,
        self::SIN_CUOTA,
        self::TIPO_NO_CLASIFICADO,
    ];

    protected $table = 'lote_prestamo_conciliaciones';

    protected $fillable = [
        'lote_mensual_id',
        'lote_prestamo_registro_id',
        'orden_operacion',
        'concepto',
        'lote_garante_registro_id',
        'eit_item',
        'socio_institucion_id',
        'id_socio',
        'monto_excel',
        'monto_excel_asignado',
        'monto_base_datos',
        'diferencia',
        'clasificacion',
        'cantidad_cuotas',
        'observacion',
        'conciliado_por',
    ];

    protected function casts(): array
    {
        return [
            'monto_excel' => 'decimal:2',
            'monto_excel_asignado' => 'decimal:2',
            'monto_base_datos' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'cantidad_cuotas' => 'integer',
            'orden_operacion' => 'integer',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMensual::class, 'lote_mensual_id');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(
            LotePrestamoRegistro::class,
            'lote_prestamo_registro_id'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(
            LotePrestamoConciliacionDetalle::class,
            'lote_prestamo_conciliacion_id'
        )->orderBy('id_solicitud')->orderBy('nro_cuota');
    }

    public function garanteRegistro(): BelongsTo
    {
        return $this->belongsTo(
            LoteGaranteRegistro::class,
            'lote_garante_registro_id'
        );
    }

    public function getConceptoTextoAttribute(): string
    {
        return match ($this->concepto) {
            self::CONCEPTO_GARANTE => 'DESCUENTO A GARANTE',
            default => 'CUOTA DE PRÉSTAMO',
        };
    }

    public function getClaseBadgeAttribute(): string
    {
        return match ($this->clasificacion) {
            self::COINCIDE => 'bg-success',
            self::FALTA => 'bg-danger',
            self::DEMASIA => 'bg-warning text-dark',
            self::SOCIO_NO_ENCONTRADO => 'bg-dark',
            self::SIN_CUOTA => 'bg-secondary',
            self::TIPO_NO_CLASIFICADO => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }

    public function getClasificacionTextoAttribute(): string
    {
        return match ($this->clasificacion) {
            self::DEMASIA => 'DEMASÍA',
            default => str_replace('_', ' ', $this->clasificacion),
        };
    }
}
