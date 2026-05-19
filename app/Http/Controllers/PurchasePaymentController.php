<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;

class PurchasePaymentController extends Controller
{
    public function store(Request $request, $purchaseId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $purchase = Purchase::findOrFail($purchaseId);

        $balance = $purchase->total - $purchase->paid_amount;

        if ($request->amount > $balance) {
            return back()->with('error', 'Payment exceeds balance');
        }

        PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        $purchase->paid_amount += $request->amount;

        $purchase->balance_amount =
            $purchase->total - $purchase->paid_amount;

        if ($purchase->paid_amount <= 0) {
            $purchase->payment_status = 'unpaid';
        } elseif ($purchase->balance_amount > 0) {
            $purchase->payment_status = 'partial';
        } else {
            $purchase->payment_status = 'paid';
        }

        $purchase->save();

        return back()->with('success', 'Payment added successfully');
    }
}