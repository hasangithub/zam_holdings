<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortOfLoading extends Model
{
    protected $table = 'port_of_loadings';
    protected $fillable = [
        'name',
        'is_active',
    ];
}