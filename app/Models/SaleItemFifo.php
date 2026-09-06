<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemFifo extends Model
{
    protected $fillable = [
        'sale_item_id',
        'purchase_item_id',
        'qty',
        'unit_cost',
        'total_cost',
    ];

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }
}
