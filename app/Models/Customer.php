<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_type',
        'name',
        'consignee_name',
        'consignee_address',
        'customer_code',
        'phone',
        'receivable_sub_ledger_id',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(SalesPayment::class);
    }
}
