<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'branch_id',
        'journal_date',
        'description',
        'created_by'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    public function references()
    {
        return $this->hasMany(JournalEntryReference::class);
    }
}
