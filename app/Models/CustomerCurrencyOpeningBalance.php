<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCurrencyOpeningBalance extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'currency',
        'opening_date',
        'amount',
        'note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
