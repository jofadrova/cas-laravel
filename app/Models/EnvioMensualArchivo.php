<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioMensualArchivo extends Model
{
    public const TIPO_PRESTAMOS = 'PRESTAMOS';

    protected $table = 'envio_mensual_archivos';

    protected $fillable = [
        'envio_mensual_id',
        'tipo',
        'nombre_original',
        'ruta',
        'mime_type',
        'hash_sha256',
        'cantidad_registros',
        'monto_total',
        'generado_por',
        'generado_en',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_registros' => 'integer',
            'monto_total' => 'decimal:2',
            'generado_en' => 'datetime',
        ];
    }

    public function envioMensual(): BelongsTo
    {
        return $this->belongsTo(EnvioMensual::class);
    }

    public function generador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}
