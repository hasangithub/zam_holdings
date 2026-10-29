<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'opening_journal_entry_id',
    ];

    public function salaryAdvances()
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function openingJournalEntry()
    {
        return $this->belongsTo(
            \App\Models\JournalEntry::class,
            'opening_journal_entry_id'
        );
    }
}
