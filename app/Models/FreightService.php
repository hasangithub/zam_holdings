<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreightService extends Model
{
    protected $fillable = [
        'name',
        'liability_sub_ledger_id',
        'active',
    ];

    public function freights()
    {
        return $this->hasMany(Freight::class);
    }
}
