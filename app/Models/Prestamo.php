<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Prestamo extends Model
{
        protected $table = 'solicitudes';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;
    protected $fillable = [
        'nro_solicitud',
        'ide_per',
        'tipo_prestamo',
        'id_garante1',
        'id_garante2',
        'monto',
        'monto_mora',
        'periodo',
        'saldo_actual',
        'ultima_cuota',
        'motivo',
        'asiento',
        'interes',
        'min_defensa',
        'itf',
        'estado',
        'gadm',
        'periodo_gadm',
        'fecha_deposito',
        'tipo_cambio',
        'editable',
        'refinanciado',
        'id_solicitud_origen',
        'saldo_refinanciado',
        'monto_desembolso_refinanciamiento',
    ];

    protected $casts = [
        'refinanciado' => 'boolean',
        'saldo_refinanciado' => 'decimal:2',
        'monto_desembolso_refinanciamiento' => 'decimal:2',
    ];

    public function socio()
    {
        return $this->belongsTo(
            Socio::class,
            'ide_per',
            'id'
        );
    }
    public function tipo()
    {
        return $this->belongsTo(
            Tasa::class,
            'tipo_prestamo',
            'id_tasa'
        );
    }

    public function garante1()
    {
        return $this->belongsTo(
            Socio::class,
            'id_garante1',
            'id'
        );
    }
    public function garante2()
    {
        return $this->belongsTo(
            Socio::class,
            'id_garante2',
            'id'
        );
    }

    public function historialGarantes()
    {
        return $this->hasMany(
            HistorialGarante::class,
            'id_solicitud',
            'id_solicitud'
        )->latest('fecha');
    }

    public function getEstadoTextoAttribute()
    {
        return match ($this->estado) {
            'AC' => 'ACTIVO',
            'PA' => 'CANCELADO',
            'CE' => 'CERRADO POR REFINANCIAMIENTO',
            default => $this->estado,
        };
    }
    public function getMontoFormateadoAttribute()
    {
        return number_format($this->monto, 2, '.', ',');
    }
    public function getSaldoActualFormateadoAttribute()
    {
        return number_format($this->saldo_actual, 2, '.', ',');
    }
    public function getInteresFormateadoAttribute()
    {
        return number_format($this->interes, 2);
    }

    public function cuotas()
    {
        return $this->hasMany(
            CuotaSolicitud::class,
            'id_solicitud',
            'id_solicitud'
        );
    }

    public function amortizacionesCapital()
    {
        return $this->hasMany(
            AmortizacionCapital::class,
            'id_solicitud',
            'id_solicitud'
        );
    }

    public function reprogramaciones()
    {
        return $this->hasMany(
            ReprogramacionPrestamo::class,
            'id_solicitud',
            'id_solicitud'
        );
    }

    public function prestamoOrigen()
    {
        return $this->belongsTo(
            self::class,
            'id_solicitud_origen',
            'id_solicitud'
        );
    }

    public function refinanciamientos()
    {
        return $this->hasMany(
            self::class,
            'id_solicitud_origen',
            'id_solicitud'
        );
    }
}
