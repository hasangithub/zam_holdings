<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesProfitLoss extends Model
{
    protected $table = 'sales_profit_losses';

    protected $fillable = [
        'sale_id',
        'total_sales_weight',
        'total_cost',
        'total_sales_amount',
        'total_profit_loss',
    ];

    protected $casts = [
        'total_sales_weight' => 'decimal:3',
        'total_cost' => 'decimal:2',
        'total_sales_amount' => 'decimal:2',
        'total_profit_loss' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesProfitLossItem::class);
    }
}