<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'payment_sub_ledger_id',
        'expense_date',
        'total_amount',
        'is_paid',
        'remarks',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function details()
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    public function paymentSubLedger()
    {
        return $this->belongsTo(SubLedger::class, 'payment_sub_ledger_id');
    }
}
