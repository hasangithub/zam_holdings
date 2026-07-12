<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
     protected $fillable = [

        'packing_no',
        'packing_date',
        'status',
        'remarks'

    ];

    public function items()
    {
        return $this->hasMany(PackingItem::class);
    }
}
