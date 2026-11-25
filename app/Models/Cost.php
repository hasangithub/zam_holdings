<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    protected $fillable = [
        'branch_id',
        'cost_category_id',
        'cost_sub_category_id',
        'cost_date',
        'amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(CostSubCategory::class, 'cost_sub_category_id');
    }

    public function payments()
    {
        return $this->hasMany(CostPayment::class);
    }

    public function getPaidAmountAttribute()
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return max(0, (float) $this->amount - $this->paid_amount);
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->balance <= 0) {
            return 'Paid';
        }

        return $this->paid_amount > 0 ? 'Partially Paid' : 'Unpaid';
    }
}