<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeOfPayment extends Model
{
     protected $table = 'modes_of_payment';
     protected $fillable = [
        'name',
        'is_active',
    ];
}
