<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInventory extends Model
{
    protected $fillable = [
        'branch_id',
        'supplier_id',
        'purchase_date',
        'total',
        'paid_amount',
        'balance_amount',
        'payment_status',
    ];
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseInventoryItem::class);
    }
}
