<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesAmendment extends Model
{
    protected $fillable = [

        'sale_id',
        'customer_id',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
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