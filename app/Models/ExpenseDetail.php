<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseDetail extends Model
{
    protected $fillable = [
        'expense_id',
        'item_id',
        'qty',
        'amount',
        'description'
    ];
     	 	 	 	 	
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
