<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Item;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


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
        $suppliers = Supplier::orderBy('name')->where('supplier_type', 'Packing Material')->get();
        $items = Item::where('item_type', 2)
            ->orderBy('name')
            ->get();

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

        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $request->validate([

            'supplier_id' =>
            'required|exists:suppliers,id',

            'purchase_date' =>
            'required|date',

            'item_id' =>
            'required|array|min:1',

            'item_id.*' =>
            'required|exists:items,id',

            'qty' =>
            'required|array|min:1',

            'qty.*' =>
            'required|numeric|min:0.001',

            'price' =>
            'required|array|min:1',

            'price.*' =>
            'required|numeric|min:0',

        ]);


        /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use ($request, $branchId) {

            /*
        |--------------------------------------------------------------------------
        | Supplier
        |--------------------------------------------------------------------------
        */

            $supplier = Supplier::lockForUpdate()
                ->findOrFail($request->supplier_id);


            /*
        |--------------------------------------------------------------------------
        | Supplier Must Have Liability Sub Ledger
        |--------------------------------------------------------------------------
        */

            if (!$supplier->liability_sub_ledger_id) {

                throw new \Exception(
                    'Supplier does not have a liability sub ledger.'
                );
            }


            /*
        |--------------------------------------------------------------------------
        | Create Purchase Inventory Header
        |--------------------------------------------------------------------------
        */

            $purchase = PurchaseInventory::create([

                'branch_id' =>
                $branchId,

                'supplier_id' =>
                $supplier->id,

                'purchase_date' =>
                $request->purchase_date,

                'total' =>
                0,

                'paid_amount' =>
                0,

                'balance_amount' =>
                0,

                'payment_status' =>
                'unpaid',

            ]);


            $total = 0;


            /*
        |--------------------------------------------------------------------------
        | Create Purchase Items
        |--------------------------------------------------------------------------
        */

            foreach ($request->item_id as $key => $itemId) {

                $qty =
                    (float) $request->qty[$key];

                $price =
                    (float) $request->price[$key];

                $subtotal =
                    $qty * $price;

                $total += $subtotal;


                PurchaseInventoryItem::create([

                    'purchase_inventory_id' =>
                    $purchase->id,

                    'item_id' =>
                    $itemId,

                    'qty' =>
                    $qty,

                    /*
                |--------------------------------------------------------------------------
                | Initially entire quantity is available
                |--------------------------------------------------------------------------
                */

                    'remaining_qty' =>
                    $qty,

                    'price' =>
                    $price,

                    'subtotal' =>
                    $subtotal,

                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Update Purchase Totals
        |--------------------------------------------------------------------------
        */

            $purchase->update([

                'total' =>
                $total,

                'paid_amount' =>
                0,

                'balance_amount' =>
                $total,

                'payment_status' =>
                $total > 0
                    ? 'unpaid'
                    : 'paid',

            ]);


            /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

            Accounting::postJournal([

                'branch_id' =>
                $branchId,

                'date' =>
                $request->purchase_date,

                'description' => 'Purchase Packing Material - '. $supplier->name,

                'entries' => [

                    /*
                |--------------------------------------------------------------------------
                | DEBIT Inventory
                |--------------------------------------------------------------------------
                */

                    [
                        'ledger_id' =>
                        4,

                        'sub_ledger_id' =>
                        2,

                        'debit' =>
                        $total,

                        'credit' =>
                        0,
                    ],

                    /*
                |--------------------------------------------------------------------------
                | CREDIT Supplier Payable
                |--------------------------------------------------------------------------
                */

                    [
                        'ledger_id' =>
                        5,

                        'sub_ledger_id' => $supplier->liability_sub_ledger_id,

                        'debit' =>
                        0,

                        'credit' =>
                        $total,
                    ],

                ],

            ]);
        });


        return redirect()
            ->route('purchase-inventories.index')
            ->with(
                'success',
                'Purchase saved successfully'
            );
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
    public function edit($id)
    {
        $purchaseInventory = PurchaseInventory::with('items')
            ->findOrFail($id);

        $suppliers = Supplier::orderBy('name')->get();

        $items = Item::orderBy('name')->get();

        return view(
            'purchase_inventories.edit',
            compact(
                'purchaseInventory',
                'suppliers',
                'items'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            $request->validate([

                'supplier_id' =>
                'required|exists:suppliers,id',

                'purchase_date' =>
                'required|date',

                'invoice_no' =>
                'nullable|string|max:255',

                'items' =>
                'required|array|min:1',

                'items.*.item_id' =>
                'required|exists:items,id',

                'items.*.qty' =>
                'required|numeric|min:0.001',

                'items.*.price' =>
                'required|numeric|min:0',

            ]);


            DB::transaction(function () use ($request, $id) {

             $branchId = auth()->user()->branch_id;

                /*
            |--------------------------------------------------------------------------
            | Purchase Inventory
            |--------------------------------------------------------------------------
            */

                $purchase =
                    PurchaseInventory::lockForUpdate()
                    ->with('items.item')
                    ->findOrFail($id);

                 $supplier = Supplier::lockForUpdate()->findOrFail($request->supplier_id);

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages([
                        'supplier_id' => 'This supplier does not have a liability subledger. Please configure the supplier accounting account first.',
                    ]);
                }


                $total = 0;


                /*
            |--------------------------------------------------------------------------
            | Existing Purchase Items
            |--------------------------------------------------------------------------
            */

                $existingItems =
                    $purchase->items->keyBy('id');


                $submittedExistingIds = [];


                /*
            |--------------------------------------------------------------------------
            | Process Submitted Items
            |--------------------------------------------------------------------------
            */

                foreach ($request->items as $row) {

                    $qty =
                        (float) $row['qty'];

                    $price =
                        (float) $row['price'];

                    $subtotal =
                        $qty * $price;

                    $total += $subtotal;


                    /*
                |--------------------------------------------------------------------------
                | Existing Item
                |--------------------------------------------------------------------------
                */

                    if (!empty($row['id'])) {

                        $purchaseItem =
                            $existingItems->get($row['id']);


                        if (!$purchaseItem) {

                            throw ValidationException::withMessages([

                                'items' =>
                                'Invalid purchase item selected.'

                            ]);
                        }


                        $submittedExistingIds[] =
                            $purchaseItem->id;


                        /*
                    |--------------------------------------------------------------------------
                    | Calculate Already Used Quantity
                    |--------------------------------------------------------------------------
                    |
                    | Original Qty - Remaining Qty
                    |
                    */

                        $usedQty =
                            (float) $purchaseItem->qty
                            -
                            (float) $purchaseItem->remaining_qty;


                        /*
                    |--------------------------------------------------------------------------
                    | Calculate New Remaining Quantity
                    |--------------------------------------------------------------------------
                    */

                        $newRemainingQty =
                            $qty - $usedQty;


                        /*
                    |--------------------------------------------------------------------------
                    | Cannot Reduce Below Already Used Quantity
                    |--------------------------------------------------------------------------
                    */

                        if ($newRemainingQty < 0) {

                            throw ValidationException::withMessages([

                                'items' =>
                                "Cannot reduce "
                                    . $purchaseItem->item->name
                                    . " below the quantity already used. "
                                    . "Already used: "
                                    . number_format(
                                        $usedQty,
                                        3
                                    )

                            ]);
                        }


                        /*
                    |--------------------------------------------------------------------------
                    | Update Existing Item
                    |--------------------------------------------------------------------------
                    */

                        $purchaseItem->update([

                            'item_id' =>
                            $row['item_id'],

                            'qty' =>
                            $qty,

                            'price' =>
                            $price,

                            'subtotal' =>
                            $subtotal,

                            'remaining_qty' =>
                            $newRemainingQty,

                        ]);
                    }


                    /*
                |--------------------------------------------------------------------------
                | New Item
                |--------------------------------------------------------------------------
                */ else {

                        PurchaseInventoryItem::create([

                            'purchase_id' =>
                            $purchase->id,

                            'item_id' =>
                            $row['item_id'],

                            'qty' =>
                            $qty,

                            'remaining_qty' =>
                            $qty,

                            'price' =>
                            $price,

                            'subtotal' =>
                            $subtotal,

                        ]);
                    }
                }


                /*
            |--------------------------------------------------------------------------
            | Removed Existing Items
            |--------------------------------------------------------------------------
            */

                foreach ($existingItems as $existingItem) {

                    if (
                        !in_array(
                            $existingItem->id,
                            $submittedExistingIds
                        )
                    ) {

                        /*
                    |--------------------------------------------------------------------------
                    | Calculate Already Used Quantity
                    |--------------------------------------------------------------------------
                    */

                        $usedQty =
                            (float) $existingItem->qty
                            -
                            (float) $existingItem->remaining_qty;


                        /*
                    |--------------------------------------------------------------------------
                    | Cannot Remove Used Item
                    |--------------------------------------------------------------------------
                    */

                        if ($usedQty > 0) {

                            throw ValidationException::withMessages([

                                'items' =>
                                "Cannot remove "
                                    . $existingItem->item->name
                                    . ". "
                                    . number_format(
                                        $usedQty,
                                        3
                                    )
                                    . " quantity has already been used."

                            ]);
                        }


                        /*
                    |--------------------------------------------------------------------------
                    | Safe to Delete
                    |--------------------------------------------------------------------------
                    */

                        $existingItem->delete();
                    }
                }

                                // Reverse old journal
                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->purchase_date,
                    'description' => 'Purchase Invoice Reversal',
                    'entries' => [
                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => 2,
                            'debit' => 0,
                            'credit' => $purchase->total,
                        ],
                        [
                            'ledger_id' => 5,
                            'sub_ledger_id' => $purchase->supplier->liability_sub_ledger_id,
                            'debit' => $purchase->total,
                            'credit' => 0,
                        ],
                    ]
                ]);


                /*
            |--------------------------------------------------------------------------
            | Update Purchase Inventory Header
            |--------------------------------------------------------------------------
            */

                $purchase->update(['supplier_id' =>$request->supplier_id,
                    'purchase_date' => $request->purchase_date,
                    'invoice_no' => $request->invoice_no,
                    'total' => $total,
                ]);

                // Post new journal
                Accounting::postJournal([
                    'branch_id' => $purchase->branch_id,
                    'date' => $purchase->purchase_date,
                    'description' => 'Purchase Invoice #' . $purchase->id . ' - Updated',
                    'entries' => [
                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => 2,
                            'debit' => $total,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 5,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $total,
                        ],
                    ]
                ]);
            });


            /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

            return redirect()
                ->route('purchase-inventories.index')
                ->with(
                    'success',
                    'Purchase updated successfully.'
                );
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            \Log::error(
                'Purchase inventory update failed',
                [
                    'purchase_id' =>
                    $id,

                    'user_id' =>
                    auth()->id(),

                    'error' =>
                    $e->getMessage(),

                    'file' =>
                    $e->getFile(),

                    'line' =>
                    $e->getLine(),

                ]
            );


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the purchase. Please try again.'
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(int $id)
    {
        try {
            DB::transaction(function () use ($id) {

                $branchId = auth()->user()->branch_id;

                $purchase = PurchaseInventory::lockForUpdate()
                    ->with('items.item', 'supplier')
                    ->findOrFail($id);

                if ($purchase->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'purchase' => 'This purchase is already cancelled.'
                    ]);
                }

                foreach ($purchase->items as $purchaseItem) {

                    $usedInSale = SaleItemFifo::where(
                        'purchase_item_id',
                        $purchaseItem->id
                    )->exists();

                    if ($usedInSale) {
                        throw ValidationException::withMessages([
                            'purchase' =>
                            "Cannot cancel this purchase because "
                                . $purchaseItem->item->name
                                . " has already been used in a sale."
                        ]);
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | Reverse Purchase Journal
            |--------------------------------------------------------------------------
            */

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => now()->toDateString(),
                    'description' => 'Purchase Invoice Cancellation',
                    'entries' => [
                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => 2,
                            'debit' => 0,
                            'credit' => $purchase->total,
                        ],
                        [
                            'ledger_id' => 5,
                            'sub_ledger_id' =>
                            $purchase->supplier->liability_sub_ledger_id,
                            'debit' => $purchase->total,
                            'credit' => 0,
                        ],
                    ]
                ]);

                /*
            |--------------------------------------------------------------------------
            | Reverse Remaining Stock
            |--------------------------------------------------------------------------
            */

                foreach ($purchase->items as $purchaseItem) {

                    $purchaseItem->update([
                        'remaining_qty' => 0,
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Cancel Purchase
            |--------------------------------------------------------------------------
            */

                $purchase->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()
                ->route('purchase_inventories.index')
                ->with(
                    'success',
                    'Purchase cancelled successfully.'
                );
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            return back()
                ->with(
                    'error',
                    'Unable to cancel the purchase. Please try again.'
                );
        }
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
