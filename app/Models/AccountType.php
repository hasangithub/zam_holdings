<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
     protected $fillable = [
        'name',
    ];

    public function accountGroups()
    {
        return $this->hasMany(AccountGroup::class);
    }
}
