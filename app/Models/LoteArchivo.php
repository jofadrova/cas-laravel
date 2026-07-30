<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteArchivo extends Model
{
    public const TIPO_PRESTAMOS = 'PRESTAMOS';
    public const TIPO_GARANTES = 'GARANTES';
    /**
     * Nombre funcional: FVS.
     * Se conserva temporalmente el valor físico UFV porque ya está almacenado
     * en lote_archivos.tipo. No cambiarlo sin una migración de datos evaluada.
     */
    public const TIPO_FVS = 'UFV';
    public const TIPO_CERTIFICADOS = 'CERTIFICADOS';
    public const ESTADO_CARGADO = 'CARGADO';

    protected $table = 'lote_archivos';

    protected $fillable = [
        'lote_mensual_id',
        'tipo',
        'nombre_original',
        'ruta',
        'extension',
        'mime_type',
        'hash_sha256',
        'filas_importadas',
        'total_monto_descuento',
        'total_tot_2',
        'total_comision',
        'estado',
        'cargado_por',
    ];

    protected function casts(): array
    {
        return [
            'filas_importadas' => 'integer',
            'total_monto_descuento' => 'decimal:6',
            'total_tot_2' => 'decimal:6',
            'total_comision' => 'decimal:6',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteMensual::class, 'lote_mensual_id');
    }

    public function registrosPrestamos(): HasMany
    {
        return $this->hasMany(
            LotePrestamoRegistro::class,
            'lote_archivo_id'
        );
    }

    public function registrosGarantes(): HasMany
    {
        return $this->hasMany(
            LoteGaranteRegistro::class,
            'lote_archivo_id'
        );
    }

    public function registrosFvs(): HasMany
    {
        return $this->hasMany(
            LoteFvsRegistro::class,
            'lote_archivo_id'
        );
    }

    public function registrosCertificados(): HasMany
    {
        return $this->hasMany(
            LoteCertificadoAporteRegistro::class,
            'lote_archivo_id'
        );
    }
}
