<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
     protected $fillable = [
        'branch_id',
        'account_group_id',
        'name',
    ];

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function subLedgers()
    {
        return $this->hasMany(SubLedger::class);
    }
}
