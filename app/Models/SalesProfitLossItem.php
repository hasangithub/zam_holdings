<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesProfitLossItem extends Model
{
    protected $table = 'sales_profit_loss_items';

    protected $fillable = [
        'sales_profit_loss_id',
        'item_id',
        'item_name',
        'sales_weight',
        'sales_price',
        'purchase_price',
        'other_expense_per_kg',
        'purchase_cost_per_kg',
        'total_cost',
        'sales_amount',
        'profit_loss',
    ];

    protected $casts = [
        'sales_weight' => 'decimal:3',
        'sales_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'other_expense_per_kg' => 'decimal:2',
        'purchase_cost_per_kg' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'sales_amount' => 'decimal:2',
        'profit_loss' => 'decimal:2',
    ];

    public function profitLoss(): BelongsTo
    {
        return $this->belongsTo(
            SalesProfitLoss::class,
            'sales_profit_loss_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}