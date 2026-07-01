<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'rate_date',
        'usd_bob',
        'usd_variation',
        'usd_variation_percent',
        'ufv_bob',
        'ufv_variation',
        'ufv_variation_percent',
        'source',
        'source_url',
        'api_timestamp',
    ];
    protected $casts = [
        'rate_date' => 'date',
        'api_timestamp' => 'datetime',

        'usd_bob' => 'decimal:4',
        'usd_variation' => 'decimal:4',
        'usd_variation_percent' => 'decimal:4',

        'ufv_bob' => 'decimal:4',
        'ufv_variation' => 'decimal:4',
        'ufv_variation_percent' => 'decimal:4',
    ];
}
