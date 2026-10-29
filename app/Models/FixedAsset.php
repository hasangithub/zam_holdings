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
        'opening_journal_entry_id',
        'fixed_asset_ledger_id',
        'asset_sub_ledger_id',
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

    public function fixedAssetLedger()
    {
        return $this->belongsTo(Ledger::class, 'fixed_asset_ledger_id');
    }

    public function assetSubLedger()
    {
        return $this->belongsTo(SubLedger::class, 'asset_sub_ledger_id');
    }


    public function openingJournalEntry()
    {
        return $this->belongsTo(
            JournalEntry::class,
            'opening_journal_entry_id'
        );
    }
}
