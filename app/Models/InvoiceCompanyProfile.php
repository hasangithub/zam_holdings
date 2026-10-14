<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCompanyProfile extends Model
{
    protected $fillable = [
        'title',
        'address',
        'sub_title_1',
        'sub_title_2',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
