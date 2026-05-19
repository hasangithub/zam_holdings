<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;

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

        return redirect()->route('purchases.index');
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'payments.creator'
        ])->findOrFail($id);

        return view('purchases.show', compact('purchase'));
    }

    public function invoice($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'payments.creator'
        ])->findOrFail($id);

        return view('purchases.invoice', compact('purchase'));
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