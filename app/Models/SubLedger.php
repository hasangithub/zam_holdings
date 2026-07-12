<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubLedger extends Model
{
     protected $fillable = [
        'branch_id',
        'ledger_id',
        'name',
    ];

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

}
