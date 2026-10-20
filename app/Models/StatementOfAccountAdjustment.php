<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementOfAccountAdjustment extends Model
{
    protected $fillable = [
        'statement_of_account_group_id',
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function statementGroup(): BelongsTo
    {
        return $this->belongsTo(
            StatementOfAccountGroup::class,
            'statement_of_account_group_id'
        );
    }
}