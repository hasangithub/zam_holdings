<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvancePayment extends Model
{
    protected $fillable = [
        'salary_advance_id',
        'payment_date',
        'amount',
        'payment_sub_ledger_id',
        'journal_entry_id',
        'reference',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function salaryAdvance()
    {
        return $this->belongsTo(
            SalaryAdvance::class
        );
    }

    public function paymentSubLedger()
    {
        return $this->belongsTo(
            SubLedger::class,
            'payment_sub_ledger_id'
        );
    }

    public function journalEntry()
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_entry_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}