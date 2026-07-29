<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotePrestamoConciliacionDetalle extends Model
{
    protected $table = 'lote_prestamo_conciliacion_detalles';

    protected $fillable = [
        'lote_prestamo_conciliacion_id',
        'id_solicitud',
        'id_cuota_solicitud',
        'tipo_prestamo',
        'descripcion_tipo',
        'grupo_comparacion',
        'nro_cuota',
        'monto_cuota',
    ];

    protected function casts(): array
    {
        return [
            'monto_cuota' => 'decimal:2',
            'nro_cuota' => 'integer',
        ];
    }

    public function conciliacion(): BelongsTo
    {
        return $this->belongsTo(
            LotePrestamoConciliacion::class,
            'lote_prestamo_conciliacion_id'
        );
    }
}
