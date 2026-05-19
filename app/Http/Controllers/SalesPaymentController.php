<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SalesPayment;

class SalesPaymentController extends Controller
{
    public function store(Request $request, $saleId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);
    
        $sale = Sale::findOrFail($saleId);
    
        if ($request->amount > $sale->balance_amount) {
            return back()->with('error', 'Payment exceeds balance');
        }
    
        SalesPayment::create([
            'sale_id' => $sale->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);
    
        $sale->total_paid += $request->amount;
    
        $sale->balance_amount =
            $sale->total - $sale->total_paid;
    
        if ($sale->balance_amount <= 0) {
            $sale->payment_status = 'paid';
        } elseif ($sale->total_paid > 0) {
            $sale->payment_status = 'partial';
        } else {
            $sale->payment_status = 'unpaid';
        }
    
        $sale->save();
    
        return back()->with('success', 'Payment saved successfully');
    }
}
