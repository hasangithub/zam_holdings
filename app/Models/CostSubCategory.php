<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostSubCategory extends Model
{
    protected $fillable = [
        'branch_id',
        'cost_category_id',
        'name',
        'ledger_id',
        'accrued_expense_sub_ledger_id',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function accruedExpenseSubLedger()
    {
        return $this->belongsTo(SubLedger::class, 'accrued_expense_sub_ledger_id');
    }

    public function costs()
    {
        return $this->hasMany(Cost::class);
    }
}