<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInventoryPayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'created_by',
    ];

    public function purchaseInventory()
    {
        return $this->belongsTo(PurchaseInventory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}