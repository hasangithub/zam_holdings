<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'advance_date',
        'amount',
        'salary_advance_sub_ledger_id',
        'payment_sub_ledger_id',
        'journal_entry_id',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function salaryAdvanceSubLedger()
    {
        return $this->belongsTo(
            SubLedger::class,
            'salary_advance_sub_ledger_id'
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

    public function payments()
    {
        return $this->hasMany(
            SalaryAdvancePayment::class
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function getReturnedAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getOutstandingAmountAttribute()
    {
        return max(
            0,
            $this->amount - $this->returned_amount
        );
    }
}