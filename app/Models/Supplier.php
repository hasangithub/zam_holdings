<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'address',
        'liability_sub_ledger_id'
    ];

    public function liabilitySubLedger()
    {
        return $this->belongsTo(
            SubLedger::class,
            'liability_sub_ledger_id'
        );
    }
}
