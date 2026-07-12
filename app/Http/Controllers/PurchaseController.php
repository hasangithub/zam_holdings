<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Accounting\Accounting;
use App\Models\PurchasePayment;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::all(),
            'items' => Item::all()
        ]);
    }

    public function store(Request $request)
    {
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'purchase_date' => now(),
            'total' => 0
        ]);

        $total = 0;

        foreach ($request->items as $row) {
            $subtotal = $row['qty'] * $row['price'];
            $total += $subtotal;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $row['item_id'],
                'qty' => $row['qty'],
                'remaining_qty' => $row['qty'],
                'price' => $row['price'],
                'subtotal' => $subtotal
            ]);
        }

        $purchase->update(['total' => $total]);

        Accounting::postJournal([
            'branch_id' => 1,
            'date' => '2026-04-04',
            'description' => 'Purchase Invoice ',

            'entries' => [
                [
                    'ledger_id' => 1,
                    'debit' => 1000,
                    'credit' => 0,
                ],
                [
                    'ledger_id' => 2,
                    'debit' => 0,
                    'credit' => 1000,
                ],
            ]
        ]);

        return redirect()->route('purchases.index');
    }

    public function show($id)
    {
        $purchase = Purchase::with('supplier')->findOrFail($id);

    $supplierId = $purchase->supplier_id;

    $purchases = Purchase::where('supplier_id', $supplierId)->get();

    $payments = PurchasePayment::where('supplier_id', $supplierId)->get();

    $totalPurchase = $purchases->sum('total');
    $totalPaid = $payments->sum('amount');

    $balance = $totalPurchase - $totalPaid;

    return view('purchases.show', compact(
        'purchase',
        'purchases',
        'payments',
        'totalPurchase',
        'totalPaid',
        'balance'
    ));
    }

    public function invoice($id)
    {
        $purchase = Purchase::with('items', 'supplier')->findOrFail($id);

    // supplier totals (ledger logic)
    $supplierId = $purchase->supplier_id;

    $totalPurchase = Purchase::where('supplier_id', $supplierId)->sum('total');

    $totalPaid = PurchasePayment::where('supplier_id', $supplierId)->sum('amount');

    $outstanding = $totalPurchase - $totalPaid;

    return view('purchases.invoice', compact(
        'purchase',
        'totalPurchase',
        'totalPaid',
        'outstanding'
    ));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        return view('purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::all(),
            'items' => Item::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        // delete old items
        $purchase->items()->delete();

        $total = 0;

        foreach ($request->items as $row) {
            $subtotal = $row['qty'] * $row['price'];
            $total += $subtotal;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $row['item_id'],
                'qty' => $row['qty'],
                'price' => $row['price'],
                'subtotal' => $subtotal
            ]);
        }

        $purchase->update([
            'supplier_id' => $request->supplier_id,
            'total' => $total
        ]);

        return redirect()->route('purchases.index');
    }

    public function destroy($id)
    {
        Purchase::findOrFail($id)->delete();
        return back();
    }
}