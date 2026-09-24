<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseAmendment extends Model
{
    protected $fillable = [

        'purchase_id',

        'supplier_id',

        'amendment_date',

        'amount',

        'reason',

        'notes',

        'status',

        'created_by',
        'journal_id',

    ];


    protected $casts = [

        'amendment_date' => 'date',
        'amount' => 'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(
            Purchase::class,
            'purchase_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class,
            'supplier_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Created User
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Journal
    |--------------------------------------------------------------------------
    */

    public function journal(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }


    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }


    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}