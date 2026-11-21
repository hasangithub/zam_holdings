<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'invoice_id',
        'internal_invoice_id',
        'airway_no',
        'consignor_id',
        'consignee_name',
        'consignee_address',
        'port_of_loading',
        'country_of_origin',
        'mode_of_payment',
        'mode_of_shipping',
        'flight_no',
        'customer_id',
        'sale_date',
        'total',
        'payment_status',
        'currency',
        'exchange_rate',
        'total_foreign',
        'status'
    ];

    public const MODEOFPAYMENTS = [
        1 => 'C & F',
    ];

    public const PORTOFLOADING = [
        1 => 'Colombo Airport, Sri Lanka',
    ];

    public const MODEOFSHIPPING = [
        1 => 'Air',
        2 => 'Sea',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalesPayment::class);
    }

    public function freights()
    {
        return $this->hasMany(Freight::class);
    }

    public function profitLoss()
    {
        return $this->hasOne(SalesProfitLoss::class);
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'consignor_id', 'id');
    }
}
