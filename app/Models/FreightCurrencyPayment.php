<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreightCurrencyPayment extends Model
{
    protected $fillable = [
        'freight_id',
        'branch_id',
        'currency',
        'payment_date',
        'amount',
        'reference',
        'note',
        'created_by',
    ];

    public function freight()
    {
        return $this->belongsTo(Freight::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
