<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingItem extends Model
{
    protected $fillable = [

        'packing_id',
        'item_id',
        'actual_weight',
        'reject',
        'market',
        'stock',
    ];

    public function packing()
    {
        return $this->belongsTo(Packing::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function customers()
    {
        return $this->hasMany(PackingItemCustomer::class);
    }
}
