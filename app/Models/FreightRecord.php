<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreightRecord extends Model
{
    protected $fillable = [
        'date',
        'airway_no',
        'consignor',
        'consignee',
        'net_weight',
        'gross_weight',
        'boxes',
        'exchange_rate',
        'custom_usd',
        'custom_lkr',
        'freight_usd',
        'freight_lkr',
        'cusdec_no',
        'booking',
        'bank',
    ];
}
