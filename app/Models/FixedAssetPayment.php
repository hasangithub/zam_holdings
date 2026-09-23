<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAssetPayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'amount',
        'payment_date',
        'payment_sub_ledger_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentSubLedger()
    {
        return $this->belongsTo(SubLedger::class, 'payment_sub_ledger_id');
    }
}