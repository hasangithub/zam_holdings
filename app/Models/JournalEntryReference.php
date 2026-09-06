<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntryReference extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'model_type',
        'model_id',
        'action',
        'reversal_of_id',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'reversal_of_id'
        );
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
