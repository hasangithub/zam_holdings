<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseDetailFifo extends Model
{
    protected $fillable = [
        'expense_detail_id',
        'purchase_inventory_item_id',
        'qty',
        'unit_cost',
    ];

    public function expenseDetail()
    {
        return $this->belongsTo(ExpenseDetail::class);
    }

    public function purchaseInventoryItem()
    {
        return $this->belongsTo(
            PurchaseInventoryItem::class
        );
    }
}