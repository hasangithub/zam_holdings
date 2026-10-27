<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Accounting\Accounting;
use App\Models\BranchItemStock;
use App\Models\InvoiceCompanyProfile;
use App\Models\JournalEntryReference;
use App\Models\PurchaseAmendment;
use App\Models\PurchasePayment;
use App\Models\SaleItemFifo;
use App\Models\SubLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WeightedAverageStockService;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::where('branch_id', auth()->user()->branch_id)->with('supplier')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {

        $branchId = auth()->user()->branch_id;

        $suppliers = Supplier::where('branch_id', $branchId)
            ->where('supplier_type', 'Trading Goods')
            ->orderBy('name')
            ->get();

        $items = Item::orderBy('name')->where('item_type', 1)->get();

        $cashSubLedgers = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact(
            'suppliers',
            'items',
            'cashSubLedgers'
        ));
    }

    public function store(Request $request)
    {
        $isNewSupplier = $request->input('is_new_supplier') == '1';

        $rules = [
            'is_new_supplier' => 'required|in:0,1',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.price' => 'required|numeric|min:0',
            'is_paid' => 'required|in:0,1',
        ];

        if ($isNewSupplier) {
            $rules['new_supplier_name'] = 'required|string|max:255';
            $rules['new_supplier_phone'] = 'nullable|string|max:30';
        } else {
            $rules['supplier_id'] = 'required|exists:suppliers,id';
        }

        if ($request->input('is_paid') == '1') {
            $rules['sub_ledger_id'] = 'required|exists:sub_ledgers,id';
            $rules['payment_amount'] = 'required|numeric|gt:0';
        }

        $request->validate($rules);

        $branchId = (int) auth()->user()->branch_id;

        DB::transaction(function () use ($request, $branchId, $isNewSupplier) {

            /*
        |--------------------------------------------------------------------------
        | 1. Find or Create Supplier
        |--------------------------------------------------------------------------
        */

            if ($isNewSupplier) {

                $supplier = Supplier::create([
                    'name' => $request->new_supplier_name,
                    'phone' => $request->new_supplier_phone,
                    'supplier_type' => 'Trading Goods',
                    'branch_id' => $branchId,
                ]);

                // Create supplier liability sub-ledger
                $subLedger = SubLedger::create([
                    'ledger_id' => 8,
                    'name' => $supplier->name,
                ]);

                $supplier->update([
                    'liability_sub_ledger_id' => $subLedger->id,
                ]);

                $supplier->refresh();
            } else {

                $supplier = Supplier::where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->findOrFail($request->supplier_id);
            }

            if (!$supplier->liability_sub_ledger_id) {
                throw ValidationException::withMessages([
                    'supplier_id' =>
                    'This supplier does not have a liability subledger. '
                        . 'Please configure the supplier accounting account first.',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 2. Create Purchase Header
        |--------------------------------------------------------------------------
        */

            $purchase = Purchase::create([
                'branch_id' => $branchId,
                'supplier_id' => $supplier->id,
                'purchase_date' => $request->purchase_date,
                'total' => 0,
                'balance_amount' => 0,
            ]);

            $total = 0;

            $weightedAverageService = app(
                WeightedAverageStockService::class
            );

            /*
        |--------------------------------------------------------------------------
        | 3. Create Purchase Items and Update Weighted-Average Stock
        |--------------------------------------------------------------------------
        */

            foreach ($request->items as $row) {

                $qty = (float) $row['qty'];
                $price = (float) $row['price'];

                $subtotal = round($qty * $price, 2);

                $total += $subtotal;

                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $row['item_id'],
                    'qty' => $qty,
                    'remaining_qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $weightedAverageService->addPurchase(
                    $branchId,
                    (int) $purchaseItem->item_id,
                    $qty,
                    $price
                );
            }

            $total = round($total, 2);

            /*
        |--------------------------------------------------------------------------
        | 4. Post Original Purchase Journal
        |--------------------------------------------------------------------------
        */

            $purchase->update([
                'total' => $total,
                'balance_amount' => $total,
            ]);

            $journal = Accounting::postJournal([
                'branch_id' => $branchId,
                'date' => $request->purchase_date,
                'description' => 'Purchase Invoice',

                'entries' => [
                    [
                        'ledger_id' => 7,
                        'sub_ledger_id' => 1,
                        'debit' => $total,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 8,
                        'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                        'debit' => 0,
                        'credit' => $total,
                    ],
                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | 5. Link Purchase Journal
        |--------------------------------------------------------------------------
        */

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => Purchase::class,
                'model_id' => $purchase->id,
                'action' => 'created',
            ]);

            /*
        |--------------------------------------------------------------------------
        | 6. Record Optional Payment
        |--------------------------------------------------------------------------
        */

            if ($request->is_paid == '1') {

                $paymentAmount = (float) $request->payment_amount;

                if ($paymentAmount > $total) {
                    throw ValidationException::withMessages([
                        'payment_amount' =>
                        'Payment amount cannot exceed the purchase total.',
                    ]);
                }

                // Ensure the selected sub-ledger belongs to Cash/Bank ledger 1
                $paymentSubLedger = SubLedger::where('ledger_id', 1)
                    ->findOrFail($request->sub_ledger_id);

                $payment = PurchasePayment::create([
                    'supplier_id' => $supplier->id,
                    'amount' => $paymentAmount,
                    'payment_date' => $request->purchase_date,
                    'note' => 'Payment for purchase',
                ]);

                // Dr Supplier Liability / Cr Cash or Bank
                $paymentJournal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->purchase_date,
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 8,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => $paymentAmount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 1,
                            'sub_ledger_id' => $paymentSubLedger->id,
                            'debit' => 0,
                            'credit' => $paymentAmount,
                        ],
                    ],
                ]);

                // Link payment journal to the payment record
                JournalEntryReference::create([
                    'journal_entry_id' => $paymentJournal->id,
                    'model_type' => PurchasePayment::class,
                    'model_id' => $payment->id,
                    'action' => 'created',
                ]);

                // Remaining supplier balance
                $purchase->update([
                    'balance_amount' => round($total - $paymentAmount, 2),
                ]);
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
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

        if ((int) $purchase->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this purchase.');
        }

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

        $invoiceCompanyProfiles = InvoiceCompanyProfile::orderBy('title')->get();

        if ((int) $purchase->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this purchase.');
        }

        $currentPurchase = $purchase->total;

        return view('purchases.invoice', compact(
            'purchase',
            'currentPurchase',
            'invoiceCompanyProfiles'
        ));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        if ((int) $purchase->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to edit this purchase.');
        }

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
            'items.*.id' => 'nullable|integer',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {

                $branchId = (int) auth()->user()->branch_id;

                $purchase = Purchase::lockForUpdate()
                    ->with('items.item')
                    ->findOrFail($id);

                if ((int) $purchase->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to update this purchase.');
                }

                $supplier = Supplier::lockForUpdate()
                    ->findOrFail($request->supplier_id);

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages([
                        'supplier_id' => 'This supplier does not have a liability subledger. Please configure the supplier accounting account first.',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Weighted-average stock adjustment
            |--------------------------------------------------------------------------
            | qty          = current stock quantity
            | average_cost = current weighted-average unit cost
            |
            | $qtyDelta   = quantity adjustment
            | $valueDelta = inventory value adjustment
            */
                $adjustStock = function ($itemId, $qtyDelta, $valueDelta) use ($branchId) {

                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        if ($qtyDelta < -0.00001) {
                            throw ValidationException::withMessages([
                                'items' => 'Stock record not found for an item being removed from this purchase.',
                            ]);
                        }

                        $stock = BranchItemStock::create([
                            'branch_id' => $branchId,
                            'item_id' => $itemId,
                            'qty' => 0,
                            'average_cost' => 0,
                        ]);
                    }

                    $currentQty = (float) $stock->qty;
                    $currentAverageCost = (float) $stock->average_cost;

                    $newQty = $currentQty + (float) $qtyDelta;
                    $newValue = ($currentQty * $currentAverageCost)
                        + (float) $valueDelta;

                    if ($newQty < -0.00001) {
                        throw ValidationException::withMessages([
                            'items' => 'The purchase cannot be updated because the adjustment would make stock quantity negative.',
                        ]);
                    }

                    if ($newValue < -0.00001) {
                        throw ValidationException::withMessages([
                            'items' => 'The purchase cannot be updated because the adjustment would make inventory value negative. Please review the purchase and stock history.',
                        ]);
                    }

                    // Avoid small floating-point negatives.
                    $newQty = max(0, $newQty);
                    $newValue = max(0, $newValue);

                    $newAverageCost = $newQty > 0
                        ? $newValue / $newQty
                        : 0;

                    $stock->update([
                        'qty' => round($newQty, 3),
                        'average_cost' => round($newAverageCost, 4),
                    ]);
                };

                $existingItems = $purchase->items->keyBy('id');
                $submittedExistingIds = [];
                $total = 0;

                /*
            |--------------------------------------------------------------------------
            | Update existing items and add new items
            |--------------------------------------------------------------------------
            */
                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $qty = (float) $row['qty'];
                    $price = (float) $row['price'];
                    $subtotal = $qty * $price;

                    $total += $subtotal;

                    if (!empty($row['id'])) {

                        $purchaseItem = $existingItems->get($row['id']);

                        if (!$purchaseItem) {
                            throw ValidationException::withMessages([
                                'items' => 'Invalid purchase item selected.',
                            ]);
                        }

                        $submittedExistingIds[] = $purchaseItem->id;

                        $oldItemId = (int) $purchaseItem->item_id;
                        $oldQty = (float) $purchaseItem->qty;
                        $oldPrice = (float) $purchaseItem->price;

                        /*
                     * Remove the old purchase line's quantity/value
                     * contribution and apply the updated line.
                     */
                        if ($oldItemId === $itemId) {

                            $qtyDelta = $qty - $oldQty;

                            $valueDelta = ($qty * $price)
                                - ($oldQty * $oldPrice);

                            $adjustStock(
                                $itemId,
                                $qtyDelta,
                                $valueDelta
                            );
                        } else {

                            // Remove the old item contribution.
                            $adjustStock(
                                $oldItemId,
                                -$oldQty,
                                - ($oldQty * $oldPrice)
                            );

                            // Add the new item contribution.
                            $adjustStock(
                                $itemId,
                                $qty,
                                $qty * $price
                            );
                        }

                        $purchaseItem->update([
                            'item_id' => $itemId,
                            'qty' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                        ]);
                    } else {

                        // Add new purchase item to weighted-average stock.
                        $adjustStock(
                            $itemId,
                            $qty,
                            $qty * $price
                        );

                        PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'item_id' => $itemId,
                            'qty' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | Remove purchase items omitted from the submitted form
            |--------------------------------------------------------------------------
            */
                foreach ($existingItems as $existingItem) {

                    if (!in_array(
                        $existingItem->id,
                        $submittedExistingIds
                    )) {

                        $oldItemId = (int) $existingItem->item_id;
                        $oldQty = (float) $existingItem->qty;
                        $oldPrice = (float) $existingItem->price;

                        // Reverse the removed purchase line's contribution.
                        $adjustStock(
                            $oldItemId,
                            -$oldQty,
                            - ($oldQty * $oldPrice)
                        );

                        $existingItem->delete();
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | Reverse old journal
            |--------------------------------------------------------------------------
            */
                Accounting::reverse(Purchase::class, $purchase->id);

                /*
            |--------------------------------------------------------------------------
            | Update purchase header
            |--------------------------------------------------------------------------
            */
                $purchase->update([
                    'supplier_id' => $request->supplier_id,
                    'total' => $total,
                    'balance_amount' => $total,
                ]);

                /*
            |--------------------------------------------------------------------------
            | Post new journal
            |--------------------------------------------------------------------------
            */
                $journal = Accounting::postJournal([
                    'branch_id' => $purchase->branch_id,
                    'date' => $purchase->purchase_date,
                    'description' => 'Purchase Invoice #' . $purchase->id . ' - Updated',
                    'entries' => [
                        [
                            'ledger_id' => 7,
                            'sub_ledger_id' => 1,
                            'debit' => $total,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 8,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $total,
                        ],
                    ],
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
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the purchase. Please try again.');
        }
    }

    public function destroy(int $id)
    {
        try {
            DB::transaction(function () use ($id) {

                $branchId = (int) auth()->user()->branch_id;

                $purchase = Purchase::lockForUpdate()
                    ->with('items.item', 'supplier')
                    ->findOrFail($id);

                if ((int) $purchase->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to cancel this purchase.');
                }

                if ($purchase->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'purchase' => 'This purchase is already cancelled.'
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Reverse Purchase Journal
            |--------------------------------------------------------------------------
            */

                Accounting::reverse(Purchase::class, $purchase->id);

                /*
            |--------------------------------------------------------------------------
            | Reverse Weighted-Average Stock
            |--------------------------------------------------------------------------
            */

                foreach ($purchase->items as $purchaseItem) {

                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $purchaseItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw ValidationException::withMessages([
                            'purchase' =>
                            'Stock record not found for '
                                . $purchaseItem->item->name . '.'
                        ]);
                    }

                    $currentQty = (float) $stock->qty;
                    $purchaseQty = (float) $purchaseItem->qty;

                    if ($purchaseQty > $currentQty + 0.00001) {
                        throw ValidationException::withMessages([
                            'purchase' =>
                            'Cannot cancel this purchase because some quantity of '
                                . $purchaseItem->item->name
                                . ' has already been sold or consumed.'
                        ]);
                    }

                    $newQty = max(0, round($currentQty - $purchaseQty, 3));

                    $stock->qty = $newQty;

                    if ($newQty <= 0.00001) {
                        $stock->qty = 0;
                        $stock->average_cost = 0;
                    }

                    $stock->save();
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
                ->with('success', 'Purchase cancelled successfully.');
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            // Allow 403/404 etc. to reach Laravel's error handler
            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->with('error', 'Unable to cancel the purchase. Please try again.');
        }
    }

    public function invoicePdf(Request $request, int $purchaseId)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.item',
        ])->findOrFail($purchaseId);

        if ($request->filled('company_profile_id')) {

            $companyProfile = InvoiceCompanyProfile::where('status', true)
                ->findOrFail($request->company_profile_id);
        } else {

            $companyProfile = (object) [
                'title' => 'MR - ZAM HOLDINGS (PVT) LTD',
                'address' => '',
            ];
        }

        $currentPurchase = $purchase->items->sum('subtotal');

        $showRate = request()->boolean('show_rate', true);

        $pdf = Pdf::loadView('purchases.invoice-pdf', [
            'purchase' => $purchase,
            'currentPurchase' => $currentPurchase,
            'showRate' => $showRate,
            'companyProfile' => $companyProfile
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

        if ((int) $purchase->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to update this purchase.');
        }


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
                        'ledger_id' => 7,
                        'sub_ledger_id' => 1,
                        'debit' => $absoluteAmount,
                        'credit' => 0,
                    ],

                    [
                        'ledger_id' => 8,
                        'sub_ledger_id' => $purchase->supplier->liability_sub_ledger_id,
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
                        'ledger_id' => 8,

                        'sub_ledger_id' =>
                        $purchase->supplier->liability_sub_ledger_id,

                        'debit' => $absoluteAmount,

                        'credit' => 0,
                    ],

                    [
                        'ledger_id' => 7,

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
