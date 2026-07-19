<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = PurchaseInventory::with('supplier')
            ->latest()
            ->paginate(20);

        return view('purchase_inventories.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('purchase_inventories.create', compact(
            'suppliers',
            'items'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $request->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
            'item_id.*' => 'required',
            'qty.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $branchId) {

            $purchase = PurchaseInventory::create([
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'total' => $request->total,
                'paid_amount' => $request->paid_amount ?? 0,
                'balance_amount' => $request->total - ($request->paid_amount ?? 0),
                'payment_status' => $request->balance_amount <= 0 ? 'paid' : 'partial',
            ]);

            foreach ($request->item_id as $key => $itemId) {

                PurchaseInventoryItem::create([
                    'purchase_inventory_id' => $purchase->id,
                    'item_id' => $itemId,
                    'qty' => $request->qty[$key],
                    'remaining_qty' => $request->qty[$key],
                    'price' => $request->price[$key],
                    'subtotal' => $request->subtotal[$key],
                ]);
            }
        });

        return redirect()
            ->route('purchase-inventories.index')
            ->with('success', 'Purchase saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseInventory $purchaseInventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseInventory $purchaseInventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseInventory $purchaseInventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseInventory $purchaseInventory)
    {
        //
    }

    public function inventorySummary()
    {
        $stocks = PurchaseInventoryItem::selectRaw("
            item_id,
            SUM(qty) as purchased_qty,
            SUM(remaining_qty) as stock_qty,
            AVG(price) as avg_price,
            SUM(remaining_qty * price) as stock_value
        ")
            ->whereHas('item', function ($q) {
                $q->where('item_type', Item::PACKAGING_ITEM);
            })
            ->with('item')
            ->groupBy('item_id')
            ->get();

        return view('purchase_inventories.summary', compact('stocks'));
    }
}
