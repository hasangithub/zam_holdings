<?php

namespace App\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Support\Facades\DB;

class Accounting
{
    public static function postJournal(array $data)
    {
       
            // Create Journal Header
            $journal = JournalEntry::create([
                'branch_id'    => $data['branch_id'],
                'journal_date' => $data['date'],
                'description'  => $data['description'] ?? null,
                'created_by'   => auth()->id(),
            ]);

            // Insert Lines
            foreach ($data['entries'] as $line) {
                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'ledger_id'        => $line['ledger_id'],
                    'sub_ledger_id'    => $line['sub_ledger_id'] ?? null,
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                    'description'      => $line['description'] ?? null,
                ]);
            }

            return $journal;
    }
}