<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryDetail extends Model
{
     protected $fillable = [
        'journal_entry_id',
        'ledger_id',
        'sub_ledger_id',
        'debit',
        'credit',
        'description'
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function subLedger()
    {
        return $this->belongsTo(SubLedger::class);
    }
}
