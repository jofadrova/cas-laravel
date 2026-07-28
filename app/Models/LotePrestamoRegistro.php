<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotePrestamoRegistro extends Model
{
    public const ESTADO_IMPORTADO = 'IMPORTADO';

    protected $table = 'lote_prestamo_registros';

    protected $fillable = [
        'lote_mensual_id',
        'lote_archivo_id',
        'fila_origen',
        'gestion',
        'mes',
        'documento_respaldo',
        'eit_codorg',
        'organismos',
        'eit_codrep',
        'reparticion',
        'grupo',
        'descripcion_grupo',
        'identificador_acreedor',
        'acreedor',
        'codigo_concepto',
        'codigo_acreedor',
        'cta_bancaria_acreedor',
        'codigo_personal',
        'eit_item',
        'carnet',
        'grado',
        'mension',
        'nombres',
        'monto_descuento',
        'tot_2',
        'comision',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fila_origen' => 'integer',
            'gestion' => 'integer',
            'monto_descuento' => 'decimal:6',
            'tot_2' => 'decimal:6',
            'comision' => 'decimal:6',
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
}
