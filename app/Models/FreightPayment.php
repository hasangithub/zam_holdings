<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreightPayment extends Model
{
    protected $fillable = [
        'freight_id',
        'payment_date',
        'amount',
        'note',
    ];

    public function freight()
    {
        return $this->belongsTo(Freight::class);
    }
}
