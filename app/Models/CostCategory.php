<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'is_active',
    ];

    public function subCategories()
    {
        return $this->hasMany(CostSubCategory::class, 'cost_category_id');
    }

    public function costs()
    {
        return $this->hasMany(Cost::class);
    }
}