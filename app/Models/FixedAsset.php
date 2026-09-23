<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'branch_id',
        'asset_code',
        'name',
        'purchase_date',
        'amount',
        'supplier_id',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments()
    {
        return $this->hasMany(FixedAssetPayment::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}