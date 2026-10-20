<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatementOfAccount extends Model
{
    protected $fillable = [
        'reference_no',
        'as_of_date',
        'total_assets',
        'total_liabilities',
        'difference',
        'branch_id',
        'status',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'as_of_date' => 'date',
        'total_assets' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(StatementOfAccountGroup::class);
    }
}
