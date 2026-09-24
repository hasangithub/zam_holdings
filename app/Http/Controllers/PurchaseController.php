<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Accounting\Accounting;
use App\Models\JournalEntryReference;
use App\Models\PurchaseAmendment;
use App\Models\PurchasePayment;
use App\Models\SaleItemFifo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'suppliers' => Supplier::where('supplier_type', 'Trading Goods')->get(),
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

        $journal = Accounting::postJournal([
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

        JournalEntryReference::create([
            'journal_entry_id' => $journal->id,
            'model_type' => Purchase::class,
            'model_id' => $purchase->id,
            'action' => 'created',
        ]);

        return redirect()->route('purchases.index');
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.item',
            'amendments' => function ($query) {
                $query->latest('amendment_date');
            },
        ])->findOrFail($id);

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
        $purchase = Purchase::with(['items.item', 'supplier'])->findOrFail($id);
        $currentPurchase = $purchase->total;

        return view('purchases.invoice', compact(
            'purchase',
            'currentPurchase',
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
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $branchId = auth()->user()->branch_id;

                $purchase = Purchase::lockForUpdate()
                    ->with('items.item')
                    ->findOrFail($id);

                $supplier = Supplier::lockForUpdate()->findOrFail($request->supplier_id);

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages([
                        'supplier_id' => 'This supplier does not have a liability subledger. Please configure the supplier accounting account first.',
                    ]);
                }

                $existingItems = $purchase->items->keyBy('id');
                $submittedExistingIds = [];
                $total = 0;

                foreach ($request->items as $row) {
                    $qty = (float) $row['qty'];
                    $price = (float) $row['price'];
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    if (!empty($row['id'])) {
                        $purchaseItem = $existingItems->get($row['id']);

                        if (!$purchaseItem) {
                            throw ValidationException::withMessages([
                                'items' => 'Invalid purchase item selected.'
                            ]);
                        }

                        $submittedExistingIds[] = $purchaseItem->id;

                        $usedQty = (float) $purchaseItem->qty - (float) $purchaseItem->remaining_qty;
                        $usedInSale = SaleItemFifo::where('purchase_item_id', $purchaseItem->id)->exists();

                        if ($usedInSale) {
                            if ((int) $row['item_id'] !== (int) $purchaseItem->item_id) {
                                throw ValidationException::withMessages([
                                    'items' => "Cannot change the item {$purchaseItem->item->name} because it has already been used in a sale."
                                ]);
                            }

                            if (abs((float) $purchaseItem->price - $price) > 0.00001) {
                                throw ValidationException::withMessages([
                                    'items' => "Cannot change the price of {$purchaseItem->item->name} because it has already been used in a sale."
                                ]);
                            }
                        }

                        if ($qty < $usedQty) {
                            throw ValidationException::withMessages([
                                'items' => "Cannot reduce {$purchaseItem->item->name} below the quantity already used. Already used: " . number_format($usedQty, 2)
                            ]);
                        }

                        $purchaseItem->update([
                            'item_id' => $usedInSale ? $purchaseItem->item_id : $row['item_id'],
                            'qty' => $qty,
                            'remaining_qty' => $qty - $usedQty,
                            'price' => $usedInSale ? $purchaseItem->price : $price,
                            'subtotal' => $subtotal,
                        ]);
                    } else {
                        PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'item_id' => $row['item_id'],
                            'qty' => $qty,
                            'remaining_qty' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }

                foreach ($existingItems as $existingItem) {
                    if (!in_array($existingItem->id, $submittedExistingIds)) {
                        $usedInSale = SaleItemFifo::where('purchase_item_id', $existingItem->id)->exists();

                        if ($usedInSale) {
                            throw ValidationException::withMessages([
                                'items' => "Cannot remove {$existingItem->item->name} because it has already been used in a sale."
                            ]);
                        }

                        $usedQty = (float) $existingItem->qty - (float) $existingItem->remaining_qty;

                        if ($usedQty > 0) {
                            throw ValidationException::withMessages([
                                'items' => "Cannot remove {$existingItem->item->name}. " . number_format($usedQty, 2) . " quantity has already been used."
                            ]);
                        }

                        $existingItem->delete();
                    }
                }

                // Reverse old journal
                Accounting::reverse(Purchase::class, $purchase->id);

                $purchase->update([
                    'supplier_id' => $request->supplier_id,
                    'total' => $total,
                    'balance_amount' => $total,
                ]);

                // Post new journal
                $journal = Accounting::postJournal([
                    'branch_id' => $purchase->branch_id,
                    'date' => $purchase->purchase_date,
                    'description' => 'Purchase Invoice #' . $purchase->id . ' - Updated',
                    'entries' => [
                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => null,
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

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => Purchase::class,
                    'model_id' => $purchase->id,
                    'action' => 'updated',
                ]);
            });

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Unable to update the purchase. Please try again.');
        }
    }
    public function destroy(int $id)
    {
        try {
            DB::transaction(function () use ($id) {

                $branchId = auth()->user()->branch_id;

                $purchase = Purchase::lockForUpdate()
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

                Accounting::reverse(Purchase::class, $purchase->id);

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
                ->route('purchases.index')
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

    public function invoicePdf(int $purchaseId)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.item',
        ])->findOrFail($purchaseId);

        $currentPurchase = $purchase->items->sum('subtotal');

        $showRate = request()->boolean('show_rate', true);

        $pdf = Pdf::loadView('purchases.invoice-pdf', [
            'purchase' => $purchase,
            'currentPurchase' => $currentPurchase,
            'showRate' => $showRate,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream(
            'purchase-invoice-' . ($purchase->invoice_no ?? $purchase->id) . '.pdf'
        );
    }

    public function storeAmendment(Request $request, $purchaseId)
    {
        $purchase = Purchase::with('supplier')
            ->findOrFail($purchaseId);


        /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([

            'amendment_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'not_in:0',
            ],

            'reason' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

        ]);

        $amount = (float) $request->amount;

        $absoluteAmount = abs($amount);

        if ($absoluteAmount <= 0) {
            return back()
                ->withErrors([
                    'amount' => 'Amendment amount cannot be zero.'
                ])
                ->withInput();
        }

        $amendment = PurchaseAmendment::create([

            'purchase_id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'amendment_date' => $validated['amendment_date'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'approved',
            'created_by' => auth()->id(),
        ]);

        if ($amount > 0) {

            /*
    |--------------------------------------------------------------------------
    | POSITIVE AMENDMENT
    |--------------------------------------------------------------------------
    |
    | Supplier payable increases
    |
    | Debit  Purchase Adjustment
    | Credit Supplier Payable
    |
    */

            $journal = Accounting::postJournal([

                'branch_id' => auth()->user()->branch_id,

                'date' => $amendment->amendment_date,

                'description' =>
                'Supplier Amendment +' .
                    number_format($absoluteAmount, 2) .
                    ' - Supplier #' . $purchase->supplier->id,

                'entries' => [

                    [
                        'ledger_id' => 4,

                        'sub_ledger_id' => 1,

                        'debit' => $absoluteAmount,

                        'credit' => 0,
                    ],

                    [
                        'ledger_id' => 5,

                        'sub_ledger_id' =>
                        $purchase->supplier->liability_sub_ledger_id,

                        'debit' => 0,

                        'credit' => $absoluteAmount,
                    ],

                ],
            ]);
        } else {

            /*
    |--------------------------------------------------------------------------
    | NEGATIVE AMENDMENT
    |--------------------------------------------------------------------------
    |
    | Supplier payable decreases
    |
    | Debit  Supplier Payable
    | Credit Purchase Adjustment
    |
    */

            $journal = Accounting::postJournal([

                'branch_id' => auth()->user()->branch_id,
                'date' => $amendment->amendment_date,
                'description' =>
                'Supplier Amendment -' .
                    number_format($absoluteAmount, 2) .
                    ' - Supplier #' . $purchase->supplier->id,

                'entries' => [

                    [
                        'ledger_id' => 5,

                        'sub_ledger_id' =>
                        $purchase->supplier->liability_sub_ledger_id,

                        'debit' => $absoluteAmount,

                        'credit' => 0,
                    ],

                    [
                        'ledger_id' => 4,

                        'sub_ledger_id' => 1,

                        'debit' => 0,

                        'credit' => $absoluteAmount,
                    ],

                ],
            ]);
        }

        JournalEntryReference::create([
            'journal_entry_id' => $journal->id,

            'model_type' => PurchaseAmendment::class,

            'model_id' => $amendment->id,

            'action' => 'created',
        ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase amendment added successfully.'
            );
    }
}
