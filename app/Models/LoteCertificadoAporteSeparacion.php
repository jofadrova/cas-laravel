<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteCertificadoAporteSeparacion extends Model
{
    protected $table = 'lote_certificado_aporte_separaciones';

    protected $fillable = [
        'lote_mensual_id',
        'lote_certificado_aporte_registro_id',
        'monto_total',
        'monto_ao',
        'monto_av',
        'monto_ai',
        'regla',
        'separado_por',
        'fecha_separacion',
    ];

    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'monto_ao' => 'decimal:2',
            'monto_av' => 'decimal:2',
            'monto_ai' => 'decimal:2',
            'fecha_separacion' => 'datetime',
        ];
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(
            LoteCertificadoAporteRegistro::class,
            'lote_certificado_aporte_registro_id'
        );
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMensual::class, 'lote_mensual_id');
    }
}
