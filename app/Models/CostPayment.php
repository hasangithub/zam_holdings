<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostPayment extends Model
{
    protected $fillable = [
        'cost_id',
        'branch_id',
        'payment_date',
        'amount',
        'cashbook_sub_ledger_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }

    public function cashbookSubLedger()
    {
        return $this->belongsTo(SubLedger::class, 'cashbook_sub_ledger_id');
    }
}