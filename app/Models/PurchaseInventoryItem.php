<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInventoryItem extends Model
{
    protected $fillable = [
        'purchase_inventory_id',
        'item_id',
        'qty',
        'remaining_qty',
        'price',
        'subtotal',
    ];

    public function purchaseInventory()
    {
        return $this->belongsTo(PurchaseInventory::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function expenseFifos()
    {
        return $this->hasMany(
            ExpenseDetailFifo::class
        );
    }
}
