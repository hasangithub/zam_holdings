<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbookTransaction extends Model
{
    protected $fillable = [
        'branch_id',
        'cashbook_sub_ledger_id',
        'transaction_date',
        'type',
        'amount',
        'ledger_id',
        'sub_ledger_id',
        'reference',
        'description',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashbook(): BelongsTo
    {
        return $this->belongsTo(
            SubLedger::class,
            'cashbook_sub_ledger_id'
        );
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function subLedger(): BelongsTo
    {
        return $this->belongsTo(
            SubLedger::class,
            'sub_ledger_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_entry_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}