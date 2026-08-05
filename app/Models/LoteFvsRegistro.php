<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteFvsRegistro extends Model
{
    public const ESTADO_IMPORTADO = 'IMPORTADO';
    public const ESTADO_VALIDO = 'VALIDO';
    public const ESTADO_NO_ENCONTRADO = 'NO_ENCONTRADO';

    public const ESTADOS_COMPARACION = [
        self::ESTADO_VALIDO,
        self::ESTADO_NO_ENCONTRADO,
    ];

    /**
     * El modelo ya utiliza la denominación funcional FVS, pero apunta a la
     * tabla física existente hasta evaluar y migrar la base de datos.
     */
    protected $table = 'lote_ufv_registros';

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
        'socio_institucion_id',
        'id_socio',
        'comparado_por',
        'fecha_comparacion',
    ];

    protected function casts(): array
    {
        return [
            'fila_origen' => 'integer',
            'gestion' => 'integer',
            'monto_descuento' => 'decimal:6',
            'tot_2' => 'decimal:6',
            'comision' => 'decimal:6',
            'fecha_comparacion' => 'datetime',
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

    public function socioInstitucion(): BelongsTo
    {
        return $this->belongsTo(
            SocioInstitucion::class,
            'socio_institucion_id'
        );
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class, 'id_socio');
    }
}
