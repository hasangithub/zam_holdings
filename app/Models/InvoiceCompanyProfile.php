<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCompanyProfile extends Model
{
    protected $fillable = [
        'title',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}