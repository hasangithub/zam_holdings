<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCurrencyPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'currency',
        'payment_date',
        'amount',
        'reference',
        'note',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
