<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProfitReportItem extends Model
{
    protected $fillable = [
        'report_id',
        'item_id',
        'sale_price_usd',
        'market_price'
    ];

    public function report()
    {
        return $this->belongsTo(InvoiceProfitReport::class, 'report_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
