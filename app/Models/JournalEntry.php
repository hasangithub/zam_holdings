<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'branch_id',
        'journal_date',
        'description',
        'is_manual',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
