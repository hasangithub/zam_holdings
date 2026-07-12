<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
     protected $fillable = [
        'name',
        'type',
        'ledger_id',
        'is_active'
    ];

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }
}
