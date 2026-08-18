<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoteCertificadoAporteRegistro extends Model
{
    public const ESTADO_IMPORTADO = 'IMPORTADO';

    protected $table = 'lote_certificado_aporte_registros';

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
        'tasa_regulacion',
        'total_descuento',
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
            'tasa_regulacion' => 'decimal:6',
            'total_descuento' => 'decimal:6',
        ];
    }

    public function getCodigoPersonalNormalizadoAttribute(): ?string
    {
        $valor = trim((string) $this->codigo_personal);

        if ($valor === '') {
            return null;
        }

        if (preg_match('/^\d+(?:\.0+)?$/', $valor)) {
            $valor = preg_replace('/\.0+$/', '', $valor);
            $valor = ltrim((string) $valor, '0');

            return $valor === '' ? '0' : $valor;
        }

        return $valor;
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMensual::class, 'lote_mensual_id');
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(LoteArchivo::class, 'lote_archivo_id');
    }

    public function separacion(): HasOne
    {
        return $this->hasOne(
            LoteCertificadoAporteSeparacion::class,
            'lote_certificado_aporte_registro_id'
        );
    }
}
