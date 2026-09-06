<?php

namespace App\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\JournalEntryReference;
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

    public static function reverse($modelType, $modelId)
    {
        return DB::transaction(function () use ($modelType, $modelId) {

            $references = JournalEntryReference::where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->whereIn('action', ['created', 'updated'])
                ->with('journalEntry.details')
                ->get();

            foreach ($references as $reference) {

                $journal = $reference->journalEntry;

                $entries = [];

                foreach ($journal->details as $detail) {
                    $entries[] = [
                        'ledger_id' => $detail->ledger_id,
                        'sub_ledger_id' => $detail->sub_ledger_id,
                        'debit' => $detail->credit,
                        'credit' => $detail->debit,
                    ];
                }

                $reversal = Accounting::postJournal([
                    'branch_id' => $journal->branch_id,
                    'date' => now()->toDateString(),
                    'description' => 'Reversal of Journal #' . $journal->id,
                    'entries' => $entries,
                ]);

                $reference->update([
                    'action' => 'reversed',
                    'reversal_of_id' => $journal->id,
                ]);
            }

            return true;
        });
    }
}