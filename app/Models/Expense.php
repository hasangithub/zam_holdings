<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'expense_date',
        'total_amount',
        'remarks'
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function details()
    {
        return $this->hasMany(ExpenseDetail::class);
    }
}
