<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
     protected $fillable = [
        'account_type_id',
        'name',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
}
