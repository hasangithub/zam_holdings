<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentPlanItemCustomer extends Model
{
    protected $fillable = [

        'shipment_plan_item_id',
        'customer_id',
        'planned_weight',
    ];

    public function shipmentPlanItem()
    {
        return $this->belongsTo(ShipmentPlanItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
