<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // INDEX
    public function index()
    {
        $sales = Sale::with('customer')->latest()->get();
        return view('sales.index', compact('sales'));
    }

    // CREATE
    public function create()
{
    $customers = Customer::all();

    $stocks = DB::table('purchase_items')
        ->join('items', 'items.id', '=', 'purchase_items.item_id')
        ->select(
            'purchase_items.item_id',
            'items.name as item_name',
            'purchase_items.price',
            DB::raw('SUM(purchase_items.remaining_qty) as total_qty')
        )
        ->where('purchase_items.remaining_qty', '>', 0)
        ->groupBy('purchase_items.item_id', 'purchase_items.price', 'items.name')
        ->orderBy('items.name')
        ->get();

    return view('sales.create', compact('customers', 'stocks'));
}

public function store(Request $request)
{
    $sale = Sale::create([
        'customer_id' => $request->customer_id,
        'sale_date' => now(),
        'total' => 0,
        'invoice_id' => $this->generateInvoiceId(),
    ]);

    $total = 0;

    foreach ($request->items as $row) {

        list($item_id, $price) = explode('|', $row['group_key']);

        $qtyNeeded = $row['qty'];

        $batches = PurchaseItem::where('item_id', $item_id)
            ->where('price', $price)
            ->where('remaining_qty', '>', 0)
            ->orderBy('id') // FIFO
            ->get();

        foreach ($batches as $batch) {

            if ($qtyNeeded <= 0) break;

            $deduct = min($batch->remaining_qty, $qtyNeeded);

            $batch->decrement('remaining_qty', $deduct);

            $qtyNeeded -= $deduct;
        }

        if ($qtyNeeded > 0) {
            return back()->with('error', 'Not enough stock for item');
        }

        $salePrice = $row['sale_price'] ?? $price;
        $subtotal = $row['qty'] * $salePrice;

        SaleItem::create([
            'sale_id' => $sale->id,
            'item_id' => $item_id,
            'qty' => $row['qty'],
            'sale_price' => $salePrice,
            'base_price' => $price,
            'subtotal' => $subtotal,
        ]);

        $total += $subtotal;
    }

    $sale->update(['total' => $total, 'balance_amount' => $total]);

    return redirect()->route('sales.index');
}

public function show($id)
{
    $sale = Sale::with(['payments'])->findOrFail($id);

    return view('sales.show', compact('sale'));
}

public function invoice($id)
{
    $sale = Sale::with(['items', 'payments'])->findOrFail($id);

    return view('sales.invoice', compact('sale'));
}

    // EDIT
    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        return view('sales.edit', [
            'sale' => $sale,
            'customers' => Customer::all(),
            'items' => Item::all()
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        // delete old items
        $sale->items()->delete();

        $total = 0;

        foreach ($request->items as $row) {

            $price = $row['sale_price'] ?? $row['base_price'];
            $qty = $row['qty'];

            $subtotal = $qty * $price;
            $total += $subtotal;

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $row['item_id'],
                'qty' => $qty,
                'base_price' => $row['base_price'],
                'sale_price' => $price,
                'subtotal' => $subtotal
            ]);
        }

        $sale->update([
            'customer_id' => $request->customer_id,
            'total' => $total
        ]);

        return redirect()->route('sales.index');
    }

    // DELETE
    public function destroy($id)
    {
        Sale::findOrFail($id)->delete();
        return back();
    }

    function generateInvoiceId()
    {
        $last = \App\Models\Sale::latest()->first();

        if (!$last) {
            return 'INV-0001';
        }

        $num = intval(substr($last->invoice_no, 4)) + 1;

        return 'INV-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}