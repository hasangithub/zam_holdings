<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryOfOrigin extends Model
{
    protected $table = 'countries_of_origin';

    protected $fillable = [
        'name',
        'is_active',
    ];
}
