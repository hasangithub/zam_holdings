<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freight extends Model
{
    protected $fillable = [
        'branch_id',
        'sale_id',
        'freight_service_id',
        'date',
        'exchange_rate',
        'amount_usd',
        'amount_lkr',
        'total_paid',
        'balance_amount',
        'opening_journal_entry_id',
        'status',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function service()
    {
        return $this->belongsTo(
            FreightService::class,
            'freight_service_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(FreightPayment::class);
    }

    public function openingJournalEntry()
    {
        return $this->belongsTo(
            JournalEntry::class,
            'opening_journal_entry_id'
        );
    }
}
