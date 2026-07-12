<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'item_code',
        'sale_price',
        'item_type',
    ];

    const SALE_ITEM = 1;
    const PACKAGING_ITEM = 2;
    const RAW_MATERIAL = 3;
    const FINISHED_GOOD = 4;
    const SERVICE = 5;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getItemTypeNameAttribute()
    {
        return match ($this->item_type) {
            1 => 'Sale Item',
            2 => 'Packaging Item',
            3 => 'Raw Material',
            4 => 'Finished Good',
            5 => 'Service',
            default => 'Unknown',
        };
    }
}


//{{ $item->item_type_name }}