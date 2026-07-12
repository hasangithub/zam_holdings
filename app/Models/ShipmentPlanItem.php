<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentPlanItem extends Model
{

    protected $fillable = [

        'shipment_plan_id',
        'item_id',
    ];

    public function shipmentPlan()
    {
        return $this->belongsTo(ShipmentPlan::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function customers()
    {
        return $this->hasMany(ShipmentPlanItemCustomer::class);
    }
}
