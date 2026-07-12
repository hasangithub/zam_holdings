<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'created_by',
        'exchange_rate',
        'amount_foreign'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
