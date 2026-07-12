<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentPlan extends Model
{

    protected $fillable = [

        'plan_no',
        'shipment_date',
        'status',
        'remarks'

    ];

    public function items()
    {
        return $this->hasMany(ShipmentPlanItem::class);
    }
}