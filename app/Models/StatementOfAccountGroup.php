<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatementOfAccountGroup extends Model
{
    protected $fillable = [
        'statement_of_account_id',
        'account_type_id',
        'account_group_id',
        'account_type_name',
        'account_group_name',
        'section',
        'ledger_total',
        'adjustment_total',
        'adjusted_total',
        'ledger_balances',
    ];

    protected $casts = [
        'ledger_balances' => 'array',
        'ledger_total' => 'decimal:2',
        'adjustment_total' => 'decimal:2',
        'adjusted_total' => 'decimal:2',
    ];

    public function statement(): BelongsTo
    {
        return $this->belongsTo(StatementOfAccount::class, 'statement_of_account_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StatementOfAccountAdjustment::class);
    }
}