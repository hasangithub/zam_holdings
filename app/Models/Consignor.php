<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consignor extends Model
{
    protected $table = 'consignors';
    
    protected $fillable = [
        'branch_id',
        'name',
        'address',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
