<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPayment extends Model
{
    protected $fillable = [
        'sale_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'created_by'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
