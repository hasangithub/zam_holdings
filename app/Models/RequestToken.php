<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestToken extends Model
{
    protected $fillable = [
        'token',
        'module',
        'action',
        'model_type',
        'model_id',
        'user_id',
    ];
}