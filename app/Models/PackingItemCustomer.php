<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingItemCustomer extends Model
{
    protected $fillable = [

        'packing_item_id',
        'customer_id',
        'packed_weight',
    ];

    public function packingItem()
    {
        return $this->belongsTo(PackingItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
