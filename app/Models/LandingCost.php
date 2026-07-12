<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingCost extends Model
{
    protected $fillable = [

        'invoice_id',
        'gross_weight',
        'exchange_rate',
        'air_freight_rate',
        'air_freight_usd',
        'air_freight_lkr',
        'logistics_expenses',
        'freight_logistics_perkg',
        'packing_cost_perkg',
        'total_cost_perkg',

    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'invoice_id');
    }
}
