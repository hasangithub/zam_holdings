<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeOfShipping extends Model
{
     protected $table = 'modes_of_shipping';
     protected $fillable = [
        'name',
        'is_active',
    ];
}
