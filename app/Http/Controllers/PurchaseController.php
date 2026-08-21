<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Accounting\Accounting;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'items' => Item::where('item_type', 1)->get()
        ]);
    }

    public function store(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $supplier = Supplier::findOrFail(
            $request->supplier_id
        );

        if (!$supplier->liability_sub_ledger_id) {

            throw ValidationException::withMessages([

                'supplier_id' =>
                'This supplier does not have a liability subledger. '
                    . 'Please configure the supplier accounting account first.',

            ]);
        }


        $purchase = Purchase::create([
            'branch_id' => $branchId,
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
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

        $purchase->update(['total' => $total, 'balance_amount' => $total]);

        Accounting::postJournal([
            'branch_id' => $branchId,
            'date' => $request->purchase_date,
            'description' => 'Purchase Invoice',

            'entries' => [
                [
                    'ledger_id' => 4,
                    'sub_ledger_id' => 1,
                    'debit' => $total,
                    'credit' => 0,
                ],
                [
                    'ledger_id' => 5,
                    'sub_ledger_id' =>  $supplier->liability_sub_ledger_id,
                    'debit' => 0,
                    'credit' => $total,
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
        $purchase = Purchase::with(['items.item', 'supplier'])
            ->findOrFail($id);

        $supplierId = $purchase->supplier_id;

        /*
    |--------------------------------------------------------------------------
    | Previous Purchases
    |--------------------------------------------------------------------------
    */

        $previousPurchasesTotal = Purchase::where('supplier_id', $supplierId)
            ->where('id', '<', $purchase->id)
            ->sum('total');


        /*
    |--------------------------------------------------------------------------
    | All Supplier Payments
    |--------------------------------------------------------------------------
    */

        $totalSupplierPaid = PurchasePayment::where(
            'supplier_id',
            $supplierId
        )->sum('amount');


        /*
    |--------------------------------------------------------------------------
    | Previous Outstanding
    |--------------------------------------------------------------------------
    |
    | Previous purchases are settled first by supplier payments.
    |
    */

        $previousPaid = min(
            $totalSupplierPaid,
            $previousPurchasesTotal
        );

        $previousOutstanding = max(
            0,
            $previousPurchasesTotal - $previousPaid
        );


        /*
    |--------------------------------------------------------------------------
    | Current Purchase
    |--------------------------------------------------------------------------
    */

        $currentPurchase = $purchase->total;


        /*
    |--------------------------------------------------------------------------
    | Payment Available For Current Purchase
    |--------------------------------------------------------------------------
    */

        $currentPaid = max(
            0,
            $totalSupplierPaid - $previousPurchasesTotal
        );

        $currentPaid = min(
            $currentPaid,
            $currentPurchase
        );


        /*
    |--------------------------------------------------------------------------
    | Current Outstanding
    |--------------------------------------------------------------------------
    */

        $currentOutstanding = max(
            0,
            $currentPurchase - $currentPaid
        );


        /*
    |--------------------------------------------------------------------------
    | Total Payable
    |--------------------------------------------------------------------------
    */

        $totalPayable =
            $previousOutstanding +
            $currentPurchase;


        return view('purchases.invoice', compact(
            'purchase',
            'previousOutstanding',
            'currentPurchase',
            'currentPaid',
            'currentOutstanding',
            'totalPayable'
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
        try {

            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',

                'items' => 'required|array|min:1',

                'items.*.item_id' =>
                'required|exists:items,id',

                'items.*.qty' =>
                'required|numeric|min:0.001',

                'items.*.price' =>
                'required|numeric|min:0',
            ]);




            DB::transaction(function () use ($request, $id) {

                $purchase = Purchase::lockForUpdate()
                    ->with('items.item')
                    ->findOrFail($id);

                $total = 0;

                $existingItems = $purchase->items->keyBy('id');

                $submittedExistingIds = [];


                foreach ($request->items as $row) {

                    $qty = (float) $row['qty'];
                    $price = (float) $row['price'];

                    $subtotal = $qty * $price;

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
                    | Already Used Quantity
                    |--------------------------------------------------------------------------
                    */

                        $usedQty =
                            (float) $purchaseItem->qty
                            -
                            (float) $purchaseItem->remaining_qty;


                        /*
                    |--------------------------------------------------------------------------
                    | Cannot change item if new quantity
                    | is less than already used quantity
                    |--------------------------------------------------------------------------
                    */

                        $newRemainingQty =
                            $qty - $usedQty;


                        if ($newRemainingQty < 0) {

                            throw ValidationException::withMessages([

                                'items' =>
                                "Cannot reduce "
                                    . $purchaseItem->item->name
                                    . " below the quantity already used. "
                                    . "Already used: "
                                    . number_format($usedQty, 2)

                            ]);
                        }


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

                        PurchaseItem::create([

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

                        $usedQty =
                            (float) $existingItem->qty
                            -
                            (float) $existingItem->remaining_qty;


                        if ($usedQty > 0) {

                            throw ValidationException::withMessages([

                                'items' =>
                                "Cannot remove "
                                    . $existingItem->item->name
                                    . ". "
                                    . number_format($usedQty, 2)
                                    . " quantity has already been used."

                            ]);
                        }


                        $existingItem->delete();
                    }
                }


                /*
            |--------------------------------------------------------------------------
            | Update Purchase
            |--------------------------------------------------------------------------
            */

                $purchase->update([

                    'supplier_id' =>
                    $request->supplier_id,

                    'total' =>
                    $total,

                ]);
            });


            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Purchase updated successfully.'
                );
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            \Log::error(
                'Purchase update failed',
                [
                    'purchase_id' => $id,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
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

    public function destroy($id)
    {
        Purchase::findOrFail($id)->delete();
        return back();
    }
}
