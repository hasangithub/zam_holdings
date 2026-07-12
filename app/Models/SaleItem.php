<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'qty',
        'base_price',
        'sale_price',
        'subtotal',
        'sale_price_foreign',
        'sub_total_foreign'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

