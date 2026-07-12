<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProfitReport extends Model
{
    protected $fillable = [
        'sale_id',
        'exchange_rate',
        'fp_perkg',
        'created_by'
    ];

    public function items()
    {
        return $this->hasMany(InvoiceProfitReportItem::class, 'report_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
