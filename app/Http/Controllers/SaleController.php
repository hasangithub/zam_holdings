<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\BranchItemStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\FreightService;
use App\Models\InvoiceCompanyProfile;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\JournalEntryReference;
use App\Models\PurchaseItem;
use App\Models\SaleItemFifo;
use App\Models\SalesPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{

    public function index()
    {
        $sales = Sale::where('currency', 'LKR')->where('branch_id', auth()->user()->branch_id)->with('customer')->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function indexExport()
    {
        $sales = Sale::where('currency', 'USD')->where('branch_id', auth()->user()->branch_id)
            ->with('freightService')
            ->latest()
            ->get();
        return view('sales.index_export', compact('sales'));
    }

    public function create()
    {
        $branchId = (int) auth()->user()->branch_id;

        $customers = Customer::orderBy('name')->get();

        $stocks = DB::table('branch_item_stocks')
            ->join('items', 'items.id', '=', 'branch_item_stocks.item_id')
            ->where('branch_item_stocks.branch_id', $branchId)
            ->where('branch_item_stocks.qty', '>', 0)
            ->select([
                'branch_item_stocks.item_id',
                'items.name as item_name',
                'branch_item_stocks.average_cost',
                'branch_item_stocks.qty as total_qty',
            ])
            ->orderBy('items.name')
            ->get();

        return view('sales.create', compact('customers', 'stocks'));
    }

    public function createExport()
    {
        $branchId = (int) auth()->user()->branch_id;

        $customers = Customer::orderBy('name')->get();

        $stocks = DB::table('branch_item_stocks')
            ->join('items', 'items.id', '=', 'branch_item_stocks.item_id')
            ->where('branch_item_stocks.branch_id', $branchId)
            ->where('branch_item_stocks.qty', '>', 0)
            ->select([
                'branch_item_stocks.item_id',
                'items.name as item_name',
                'branch_item_stocks.average_cost',
                'branch_item_stocks.qty as total_qty',
            ])
            ->orderBy('items.name')
            ->get();

        return view('sales.export_create', compact(
            'customers',
            'stocks'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'distinct', 'exists:items,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
        ]);

        $branchId = (int) auth()->user()->branch_id;

        DB::transaction(function () use ($validated, $branchId) {

            $customer = Customer::findOrFail($validated['customer_id']);

            if (!$customer->receivable_sub_ledger_id) {
                throw ValidationException::withMessages([
                    'customer_id' =>
                    'This customer does not have a receivable subledger configured.',
                ]);
            }

            $sale = Sale::create([
                'branch_id' => $branchId,
                'customer_id' => $customer->id,
                'sale_date' => $validated['sale_date'],
                'total' => 0,
                'invoice_id' => $this->generateInvoiceId($customer->customer_code)
            ]);

            $total = 0;
            $totalCogs = 0;

            foreach ($validated['items'] as $row) {
                $itemId = (int) $row['item_id'];
                $qty = (float) $row['qty'];
                $salePrice = (float) $row['sale_price'];

                // Lock the stock row for this item and this branch.
                $stock = BranchItemStock::where('branch_id', $branchId)
                    ->where('item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stock record not found for item ID {$itemId}.",
                    ]);
                }

                $availableQty = (float) $stock->qty;

                if ($qty > $availableQty) {
                    $itemName = DB::table('items')
                        ->where('id', $itemId)
                        ->value('name') ?? "Item ID {$itemId}";

                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$itemName}. Available quantity: "
                            . number_format($availableQty, 3),
                    ]);
                }

                // Use the server-side weighted average cost.
                $averageCost = (float) $stock->average_cost;

                $subtotal = round($qty * $salePrice, 2);
                $itemCogs = round($qty * $averageCost, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $itemId,
                    'qty' => $qty,
                    'sale_price' => $salePrice,
                    'subtotal' => $subtotal,
                    'base_price' => $averageCost,
                ]);

                // Deduct quantity from this branch's weighted-average stock.
                $stock->qty = round($availableQty - $qty, 3);
                $stock->save();

                $total += $subtotal;
                $totalCogs += $itemCogs;
            }

            $total = round($total, 2);
            $totalCogs = round($totalCogs, 2);

            $sale->update([
                'total' => $total,
            ]);

            // Post the sales and inventory accounting entries.
            $journal = Accounting::postJournal([
                'branch_id' => $branchId,
                'date' => $validated['sale_date'],
                'description' => 'Sales Invoice',
                'entries' => [
                    [
                        'ledger_id' => 6,
                        'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                        'debit' => $total,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 11,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $total,
                    ],
                    [
                        'ledger_id' => 13,
                        'sub_ledger_id' => null,
                        'debit' => $totalCogs,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 7,
                        'sub_ledger_id' => 1,
                        'debit' => 0,
                        'credit' => $totalCogs,
                    ],
                ],
            ]);

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => Sale::class,
                'model_id' => $sale->id,
                'action' => 'created',
            ]);
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale created successfully.');
    }

    public function storeExport(Request $request)
    {
        $request->validate([
            'customer_id'                => 'required|exists:customers,id',
            'exchange_rate'              => 'required|numeric|gt:0',
            'sale_date'                  => 'required|date',

            'items'                      => 'required|array|min:1',
            'items.*.item_id'            => 'required|integer|distinct|exists:items,id',
            'items.*.qty'                => 'required|numeric|gt:0',
            'items.*.sale_price_foreign' => 'required|numeric|min:0',

            'airway_no'                  => 'nullable|string|max:255',
            'flight_no'                  => 'nullable|string|max:255',
            'port_of_loading'            => 'nullable',
            'country_of_origin'          => 'required|string|max:255',
            'mode_of_payment'            => 'nullable',
            'mode_of_shipping'           => 'nullable',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {

                $branchId = (int) auth()->user()->branch_id;

                $customer = Customer::lockForUpdate()
                    ->findOrFail($request->customer_id);

                $exchangeRate = (float) $request->exchange_rate;

                $sale = Sale::create([
                    'branch_id'          => $branchId,
                    'customer_id'        => $customer->id,
                    'sale_date'          => $request->sale_date,
                    'currency'           => 'USD',
                    'exchange_rate'      => $exchangeRate,
                    'total'              => 0,
                    'total_foreign'      => 0,
                    'invoice_id'         => $this->generateInvoiceId(
                        $customer->customer_code
                    ),
                    'airway_no'          => $request->airway_no,
                    'consignee_name'     => $customer->consignee_name,
                    'consignee_address'  => $customer->consignee_address,
                    'port_of_loading'    => $request->port_of_loading,
                    'country_of_origin'  => $request->country_of_origin,
                    'mode_of_payment'    => $request->mode_of_payment,
                    'mode_of_shipping'   => $request->mode_of_shipping,
                    'flight_no'          => $request->flight_no,
                ]);

                $totalUsd = 0;
                $totalLkr = 0;
                $totalCogs = 0;

                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $quantity = (float) $row['qty'];
                    $salePriceUsd = (float) $row['sale_price_foreign'];

                    /*
                |--------------------------------------------------------------------------
                | Get and lock the weighted-average stock for this branch and item
                |--------------------------------------------------------------------------
                */

                    $stock = DB::table('branch_item_stocks')
                        ->where('branch_id', $branchId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw ValidationException::withMessages([
                            'items' => "Stock record not found for item ID: {$itemId}.",
                        ]);
                    }

                    $availableQty = (float) $stock->qty;
                    $averageCost = (float) $stock->average_cost;

                    if ($availableQty < $quantity) {
                        throw ValidationException::withMessages([
                            'items' =>
                            "Not enough stock for item ID: {$itemId}. "
                                . 'Available: '
                                . number_format($availableQty, 3)
                                . ', Required: '
                                . number_format($quantity, 3),
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Calculate USD and LKR sale amounts
                |--------------------------------------------------------------------------
                */

                    $subtotalUsd = $quantity * $salePriceUsd;
                    $subtotalLkr = $subtotalUsd * $exchangeRate;

                    /*
                |--------------------------------------------------------------------------
                | Calculate COGS using weighted-average cost
                |--------------------------------------------------------------------------
                */

                    $itemCogs = $quantity * $averageCost;
                    $totalCogs += $itemCogs;

                    /*
                |--------------------------------------------------------------------------
                | Create sale item
                |--------------------------------------------------------------------------
                */

                    SaleItem::create([
                        'sale_id'             => $sale->id,
                        'item_id'             => $itemId,
                        'qty'                 => $quantity,
                        'base_price'          => $averageCost,
                        'sale_price_foreign'  => $salePriceUsd,
                        'sub_total_foreign'   => $subtotalUsd,
                        'sale_price'          => $salePriceUsd * $exchangeRate,
                        'subtotal'            => $subtotalLkr,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Deduct stock from branch_item_stocks
                |--------------------------------------------------------------------------
                */

                    $newQty = $availableQty - $quantity;

                    DB::table('branch_item_stocks')
                        ->where('branch_id', $branchId)
                        ->where('item_id', $itemId)
                        ->update([
                            'qty' => max(0, $newQty),
                            'updated_at' => now(),
                        ]);

                    $totalUsd += $subtotalUsd;
                    $totalLkr += $subtotalLkr;
                }

                /*
            |--------------------------------------------------------------------------
            | Update sale totals
            |--------------------------------------------------------------------------
            */

                $sale->update([
                    'total_foreign' => $totalUsd,
                    'total'         => $totalLkr,
                ]);

                /*
            |--------------------------------------------------------------------------
            | Post accounting journal
            |--------------------------------------------------------------------------
            */

                $journal = Accounting::postJournal([
                    'branch_id'   => $branchId,
                    'date'        => $sale->sale_date,
                    'description' => 'Export Sale - Invoice ' . $sale->invoice_id,

                    'entries' => [
                        // Customer receivable
                        [
                            'ledger_id'     => 6,
                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                            'debit'         => $totalLkr,
                            'credit'        => 0,
                        ],

                        // Sales revenue
                        [
                            'ledger_id'     => 12,
                            'sub_ledger_id' => null,
                            'debit'         => 0,
                            'credit'        => $totalLkr,
                        ],

                        // Cost of goods sold
                        [
                            'ledger_id'     => 14,
                            'sub_ledger_id' => null,
                            'debit'         => $totalCogs,
                            'credit'        => 0,
                        ],

                        // Inventory
                        [
                            'ledger_id'     => 7,
                            'sub_ledger_id' => 1,
                            'debit'         => 0,
                            'credit'        => $totalCogs,
                        ],
                    ],
                ]);

                /*
            |--------------------------------------------------------------------------
            | Link journal to sale
            |--------------------------------------------------------------------------
            */

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type'       => Sale::class,
                    'model_id'         => $sale->id,
                    'action'           => 'created',
                ]);

                return $sale;
            });

            return redirect()
                ->route('export-sales.index')
                ->with('success', 'Export sale created successfully.');
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to create export sale. Please try again.');
        }
    }

    public function show(int $id)
    {
        $sale = Sale::with(['items'])->findOrFail($id);

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }

        return view('sales.show', compact('sale'));
    }

    public function invoice($id)
    {
        $sale = Sale::with(['items.item', 'customer'])->findOrFail($id);

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }

        /*
    |--------------------------------------------------------------------------
    | Previous Sales
    |--------------------------------------------------------------------------
    */

        $previousSalesTotal = Sale::where('customer_id', $sale->customer_id)
            ->where('id', '<', $sale->id)
            ->sum('total');


        /*
    |--------------------------------------------------------------------------
    | All Customer Payments
    |--------------------------------------------------------------------------
    */

        $customerPayments = SalesPayment::where(
            'customer_id',
            $sale->customer_id
        )->sum('amount');


        /*
    |--------------------------------------------------------------------------
    | Previous Payments
    |--------------------------------------------------------------------------
    |
    | Payments are customer-level, so payments are considered against
    | previous outstanding first.
    |
    */

        $previousPaymentsTotal = min(
            $customerPayments,
            $previousSalesTotal
        );


        /*
    |--------------------------------------------------------------------------
    | Previous Outstanding
    |--------------------------------------------------------------------------
    */

        $previousOutstanding = max(
            0,
            $previousSalesTotal - $previousPaymentsTotal
        );


        /*
    |--------------------------------------------------------------------------
    | Current Invoice
    |--------------------------------------------------------------------------
    */

        $currentInvoice = $sale->total;


        /*
    |--------------------------------------------------------------------------
    | Payment Available For Current Invoice
    |--------------------------------------------------------------------------
    */

        $currentPayment = max(
            0,
            $customerPayments - $previousSalesTotal
        );

        $currentPayment = min(
            $currentPayment,
            $currentInvoice
        );


        /*
    |--------------------------------------------------------------------------
    | Current Balance
    |--------------------------------------------------------------------------
    */

        $currentBalance = max(
            0,
            $currentInvoice - $currentPayment
        );


        /*
    |--------------------------------------------------------------------------
    | Total Payable
    |--------------------------------------------------------------------------
    */

        $totalPayable =
            $previousOutstanding +
            $currentInvoice;


        /*
    |--------------------------------------------------------------------------
    | Group Same Items
    |--------------------------------------------------------------------------
    */

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {

                return (object) [
                    'item'       => $rows->first()->item,
                    'qty'        => $rows->sum('qty'),
                    'sale_price' => $rows->first()->sale_price,
                    'subtotal'   => $rows->sum('subtotal'),
                ];
            })
            ->values();

        /*
|--------------------------------------------------------------------------
| Group Parent + Child Items
|--------------------------------------------------------------------------
*/

        $groupedItems = $groupedItems
            ->groupBy(function ($row) {

                // Parent item
                if (is_null($row->item->parent_id)) {
                    return $row->item->id;
                }

                // Child item → group under parent
                return $row->item->parent_id;
            })
            ->map(function ($rows) {

                // Find the parent row
                $parentRow = $rows->first(function ($row) {
                    return is_null($row->item->parent_id);
                });

                /*
        |--------------------------------------------------------------------------
        | Parent exists
        |--------------------------------------------------------------------------
        */

                if ($parentRow) {

                    // Parent + all children weights
                    $totalWeight = $rows->sum('qty');

                    return (object) [
                        'item'       => $parentRow->item,
                        'qty'        => $rows->sum('qty'),


                        // Parent rate
                        'sale_price' => $parentRow->sale_price,

                        // Parent rate × combined weight
                        'subtotal'   => $totalWeight * $parentRow->sale_price,
                    ];
                }

                /*
        |--------------------------------------------------------------------------
        | Child exists without parent in this sale
        |--------------------------------------------------------------------------
        */

                $first = $rows->first();

                return (object) [
                    'item'       => $first->item,
                    'qty'        => $rows->sum('qty'),
                    'sale_price' => $first->sale_price,
                    'subtotal'   => $rows->sum('subtotal'),
                ];
            })
            ->values();


        $invoiceCompanyProfiles = InvoiceCompanyProfile::where('status', true)->get();

        return view('sales.invoice', compact(
            'sale',
            'groupedItems',
            'previousOutstanding',
            'currentInvoice',
            'currentPayment',
            'currentBalance',
            'totalPayable',
            'invoiceCompanyProfiles'
        ));
    }

    public function invoiceExport($id)
    {
        $sale = Sale::with([
            'customer',
            'items.item'
        ])->findOrFail($id);

        $invoiceCompanyProfiles = InvoiceCompanyProfile::where('status', true)
            ->orderBy('title')
            ->get();

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price_foreign' => $rows->sum('sub_total_foreign') / max($rows->sum('qty'), 1),
                    'sub_total_foreign' => $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();

        /*
|--------------------------------------------------------------------------
| Group Parent + Child Items
|--------------------------------------------------------------------------
*/

        $groupedItems = $groupedItems
            ->groupBy(function ($row) {

                // Parent item
                if (is_null($row->item->parent_id)) {
                    return $row->item->id;
                }

                // Child item → group under parent
                return $row->item->parent_id;
            })
            ->map(function ($rows) {

                // Find parent item
                $parentRow = $rows->first(function ($row) {
                    return is_null($row->item->parent_id);
                });

                if ($parentRow) {

                    // Parent + all children quantity
                    $totalQty = $rows->sum('qty');

                    // Use parent's foreign rate
                    $parentRate = $parentRow->sale_price_foreign;

                    return (object) [
                        'item' => $parentRow->item,
                        'qty' => $totalQty,
                        'sale_price_foreign' => $parentRate,
                        'sub_total_foreign' => $totalQty * $parentRate,
                    ];
                }

                /*
        |--------------------------------------------------------------------------
        | Child without parent in this sale
        |--------------------------------------------------------------------------
        */

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price_foreign' => $first->sale_price_foreign,
                    'sub_total_foreign' => $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();

        return view(
            'sales.export_invoice',
            compact('sale', 'groupedItems', 'invoiceCompanyProfiles')
        );
    }

    public function edit($id)
    {
        $branchId = (int) auth()->user()->branch_id;

        $sale = Sale::with(['customer', 'items.item'])
            ->findOrFail($id);

        if ((int) $sale->branch_id !== $branchId) {
            abort(403, 'You are not allowed to edit this sale.');
        }

        $customers = Customer::orderBy('name')->get();

        $items = Item::query()
            ->leftJoin('branch_item_stocks', function ($join) use ($branchId) {
                $join->on('items.id', '=', 'branch_item_stocks.item_id')
                    ->where('branch_item_stocks.branch_id', '=', $branchId);
            })
            ->select(
                'items.*',
                'branch_item_stocks.qty as available_qty',
                'branch_item_stocks.average_cost as average_cost'
            )
            ->where(function ($query) use ($sale) {
                $query->where('branch_item_stocks.qty', '>', 0)
                    ->orWhereIn(
                        'items.id',
                        $sale->items->pluck('item_id')->unique()
                    );
            })
            ->orderBy('items.name')
            ->get();

        return view('sales.edit', compact(
            'sale',
            'customers',
            'items'
        ));
    }

    public function editExport($id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to edit this sale.');
        }

        $branchId = (int) auth()->user()->branch_id;

        $items = Item::query()
            ->leftJoin('branch_item_stocks', function ($join) use ($branchId) {
                $join->on('items.id', '=', 'branch_item_stocks.item_id')
                    ->where('branch_item_stocks.branch_id', '=', $branchId);
            })
            ->select(
                'items.*',
                'branch_item_stocks.qty as available_qty',
                'branch_item_stocks.average_cost as average_cost'
            )
            ->where(function ($query) use ($sale) {
                $query->where('branch_item_stocks.qty', '>', 0)
                    ->orWhereIn(
                        'items.id',
                        $sale->items->pluck('item_id')->unique()
                    );
            })
            ->orderBy('items.name')
            ->get();

        return view('sales.export_edit', [
            'sale' => $sale,
            'customers' => Customer::all(),
            'items' => $items,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer|distinct|exists:items,id',
            'items.*.qty'       => 'required|numeric|gt:0',
            'items.*.sale_price' => 'required|numeric|min:0',
            'sale_date'         => 'nullable|date',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {

                $branchId = (int) auth()->user()->branch_id;

                // Lock sale and verify branch access
                $sale = Sale::lockForUpdate()->findOrFail($id);

                if ((int) $sale->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to update this sale.');
                }

                $customer = Customer::lockForUpdate()
                    ->findOrFail($request->customer_id);

                $oldSaleItems = SaleItem::where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->get();

                /*
            |--------------------------------------------------------------------------
            | RESTORE OLD SALE QUANTITIES TO BRANCH STOCK
            |--------------------------------------------------------------------------
            | Weighted-average stock is used instead of FIFO purchase batches.
            */

                foreach ($oldSaleItems as $oldItem) {

                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $oldItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw ValidationException::withMessages([
                            'items' => 'Stock record not found for item ID '
                                . $oldItem->item_id . '.',
                        ]);
                    }

                    $currentQty = (float) $stock->qty;
                    $currentAverageCost = (float) $stock->average_cost;

                    $restoreQty = (float) $oldItem->qty;
                    $oldCost = (float) $oldItem->base_price;

                    $newQty = $currentQty + $restoreQty;

                    // Restore the previous sale quantity at its recorded cost
                    if ($newQty > 0) {
                        $stock->average_cost = (
                            ($currentQty * $currentAverageCost)
                            + ($restoreQty * $oldCost)
                        ) / $newQty;
                    }

                    $stock->qty = $newQty;
                    $stock->save();
                }

                /*
            |--------------------------------------------------------------------------
            | REMOVE OLD SALE ITEM / FIFO RECORDS
            |--------------------------------------------------------------------------
            */

                $oldSaleItemIds = $oldSaleItems->pluck('id');

                if ($oldSaleItemIds->isNotEmpty()) {
                    SaleItemFifo::whereIn('sale_item_id', $oldSaleItemIds)
                        ->delete();
                }

                // Reverse the existing sale journal
                Accounting::reverse(Sale::class, $sale->id);

                // Remove old sale items
                SaleItem::where('sale_id', $sale->id)->delete();

                /*
            |--------------------------------------------------------------------------
            | PROCESS UPDATED SALE ITEMS USING WEIGHTED-AVERAGE COST
            |--------------------------------------------------------------------------
            */

                $total = 0;
                $costOfGoodsSold = 0;

                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $qtyRequested = (float) $row['qty'];
                    $salePrice = (float) $row['sale_price'];

                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        $itemName = optional(Item::find($itemId))->name
                            ?? "Item ID {$itemId}";

                        throw ValidationException::withMessages([
                            'items' => "Stock record not found for {$itemName}.",
                        ]);
                    }

                    $availableQty = (float) $stock->qty;

                    if ($availableQty < $qtyRequested) {
                        $itemName = optional(Item::find($itemId))->name
                            ?? "Item ID {$itemId}";

                        throw ValidationException::withMessages([
                            'items' => "Not enough stock for {$itemName}. "
                                . 'Available: ' . number_format($availableQty, 3)
                                . ', Required: ' . number_format($qtyRequested, 3),
                        ]);
                    }

                    // Use the current weighted-average cost
                    $averageCost = (float) $stock->average_cost;

                    $saleSubtotal = $qtyRequested * $salePrice;
                    $itemCost = $qtyRequested * $averageCost;

                    // Create updated sale item
                    SaleItem::create([
                        'sale_id'          => $sale->id,
                        'item_id'          => $itemId,
                        'qty'              => $qtyRequested,
                        'sale_price'       => $salePrice,
                        'base_price'       => $averageCost,
                        'subtotal'         => $saleSubtotal,
                        'sale_price_foreign' => 0,
                        'sub_total_foreign'  => 0,
                    ]);

                    // Deduct stock; average cost remains unchanged on a sale
                    $stock->qty = $availableQty - $qtyRequested;
                    $stock->save();

                    $total += $saleSubtotal;
                    $costOfGoodsSold += $itemCost;
                }

                /*
            |--------------------------------------------------------------------------
            | UPDATE SALE
            |--------------------------------------------------------------------------
            */

                $sale->update([
                    'customer_id'        => $customer->id,
                    'total'              => $total,
                    'total_foreign'      => 0,
                    'balance_amount'     => $total,
                    'cost_of_goods_sold' => $costOfGoodsSold,
                    'sale_date'          => $request->sale_date ?? $sale->sale_date,
                ]);

                /*
            |--------------------------------------------------------------------------
            | POST ACCOUNTING JOURNAL
            |--------------------------------------------------------------------------
            */

                $journal = Accounting::postJournal([
                    'branch_id'   => $branchId,
                    'date'        => $sale->sale_date,
                    'description' => 'Credit Sale - Invoice ' . $sale->invoice_id,
                    'entries'     => [
                        // Customer receivable
                        [
                            'ledger_id'     => 6,
                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                            'debit'         => $total,
                            'credit'        => 0,
                        ],

                        // Sales
                        [
                            'ledger_id'     => 11,
                            'sub_ledger_id' => null,
                            'debit'         => 0,
                            'credit'        => $total,
                        ],

                        // Cost of goods sold
                        [
                            'ledger_id'     => 13,
                            'sub_ledger_id' => null,
                            'debit'         => $costOfGoodsSold,
                            'credit'        => 0,
                        ],

                        // Inventory
                        [
                            'ledger_id'     => 7,
                            'sub_ledger_id' => 1,
                            'debit'         => 0,
                            'credit'        => $costOfGoodsSold,
                        ],
                    ],
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type'       => Sale::class,
                    'model_id'         => $sale->id,
                    'action'            => 'updated',
                ]);
            });

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale updated successfully.');
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to update sale. Please try again.');
        }
    }

    public function updateExport(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'exchange_rate' => 'required|numeric|gt:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.sale_price_foreign' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {

                $branchId = (int) auth()->user()->branch_id;

                // Lock sale
                $sale = Sale::lockForUpdate()->findOrFail($id);

                if ((int) $sale->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to update this sale.');
                }

                if ($sale->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'sale' => 'A cancelled sale cannot be updated.',
                    ]);
                }

                // Lock customer
                $customer = Customer::lockForUpdate()
                    ->findOrFail($request->customer_id);

                // Lock existing sale items
                $oldItems = $sale->items()
                    ->lockForUpdate()
                    ->get();

                /*
             * STEP 1:
             * Restore the old quantities to branch stock.
             * Weighted-average cost remains unchanged.
             */
                foreach ($oldItems as $oldItem) {

                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $oldItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception(
                            "Branch stock not found for item ID {$oldItem->item_id}."
                        );
                    }

                    $stock->qty = (float) $stock->qty
                        + (float) $oldItem->qty;

                    $stock->save();
                }

                /*
             * STEP 2:
             * Reverse the old sale journal and remove old sale items.
             */
                Accounting::reverse(Sale::class, $sale->id);

                $sale->items()->delete();

                /*
             * STEP 3:
             * Create each submitted row as one SaleItem.
             * No FIFO batch allocation or item splitting.
             */
                $totalUsd = 0;
                $totalLkr = 0;
                $cogs = 0;

                $exchangeRate = (float) $request->exchange_rate;

                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $qty = (float) $row['qty'];
                    $usdPrice = (float) $row['sale_price_foreign'];

                    // Lock the current branch stock
                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $itemId)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw ValidationException::withMessages([
                            'items' => "Stock not found for item ID {$itemId}.",
                        ]);
                    }

                    $availableQty = (float) $stock->qty;

                    // Validate available quantity
                    if ($availableQty < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for item ID {$itemId}. "
                                . "Available quantity: {$availableQty}, "
                                . "requested quantity: {$qty}.",
                        ]);
                    }

                    // Use the database weighted-average cost
                    $averageCost = (float) $stock->average_cost;

                    $subtotalUsd = $qty * $usdPrice;
                    $subtotalLkr = $subtotalUsd * $exchangeRate;
                    $salePriceLkr = $usdPrice * $exchangeRate;

                    $itemCost = $qty * $averageCost;

                    // Deduct quantity from branch stock
                    $stock->qty = $availableQty - $qty;
                    $stock->save();

                    // Create one sale item per submitted row
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'item_id' => $itemId,
                        'qty' => $qty,

                        // Weighted-average inventory cost
                        'base_price' => $averageCost,

                        'sale_price_foreign' => $usdPrice,
                        'sub_total_foreign' => $subtotalUsd,

                        'sale_price' => $salePriceLkr,
                        'subtotal' => $subtotalLkr,
                    ]);

                    $totalUsd += $subtotalUsd;
                    $totalLkr += $subtotalLkr;
                    $cogs += $itemCost;
                }

                /*
             * STEP 4:
             * Update sale totals.
             */
                $sale->update([
                    'customer_id' => $customer->id,
                    'exchange_rate' => $exchangeRate,
                    'total_foreign' => $totalUsd,
                    'total' => $totalLkr,
                    'balance_amount' => $totalLkr - (float) $sale->total_paid,
                    'cost_of_goods_sold' => $cogs,
                ]);

                /*
             * STEP 5:
             * Post the updated accounting journal.
             */
                $journal = Accounting::postJournal([
                    'branch_id' => $sale->branch_id,
                    'date' => $sale->sale_date,
                    'description' => 'Export Sale - Invoice ' . $sale->invoice_id,
                    'entries' => [
                        [
                            'ledger_id' => 6,
                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                            'debit' => $totalLkr,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 11,
                            'sub_ledger_id' => null,
                            'debit' => 0,
                            'credit' => $totalLkr,
                        ],
                        [
                            'ledger_id' => 13,
                            'sub_ledger_id' => null,
                            'debit' => $cogs,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 7,
                            'sub_ledger_id' => 1,
                            'debit' => 0,
                            'credit' => $cogs,
                        ],
                    ],
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => Sale::class,
                    'model_id' => $sale->id,
                    'action' => 'updated',
                ]);
            });

            return redirect()
                ->route('export-sales.index')
                ->with('success', 'Export sale updated successfully.');
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to update export sale. Please try again.');
        }
    }

    public function destroy(int $id)
    {
        try {
            DB::transaction(function () use ($id) {

                $branchId = (int) auth()->user()->branch_id;

                // Lock the sale
                $sale = Sale::lockForUpdate()->findOrFail($id);

                // Ensure the sale belongs to the logged-in user's branch
                if ((int) $sale->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to cancel this sale.');
                }

                // Prevent cancelling an already-cancelled sale
                if ($sale->status === 'cancelled') {
                    throw new \Exception('This sale has already been cancelled.');
                }

                // Lock the sale items
                $saleItems = SaleItem::where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($saleItems as $saleItem) {

                    // Lock the relevant branch stock
                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $saleItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception(
                            "Branch stock not found for item ID {$saleItem->item_id}."
                        );
                    }

                    // Restore sold quantity to branch stock.
                    // Do not change average_cost when cancelling a sale.
                    $stock->qty = (float) $stock->qty + (float) $saleItem->qty;
                    $stock->save();
                }

                // Reverse the accounting entries associated with this sale
                Accounting::reverse(Sale::class, $sale->id);

                // Mark the sale as cancelled
                $sale->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale cancelled successfully.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            // Preserve HTTP errors such as 403 and 404
            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to cancel sale: ' . $e->getMessage()
            );
        }
    }

    private function generateInvoiceId($customerName)
    {
        $prefix = "ZAM";

        // CUSTOMER CODE (3 letters)
        $customerCode = strtoupper(
            substr(preg_replace('/[^A-Za-z]/', '', $customerName), 0, 3)
        );

        // YEAR (2026 → 26)
        $year = date('y');

        // Get last invoice of same year
        $lastSale = Sale::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastSale) {

            $parts = explode('/', $lastSale->invoice_id);

            $lastNumber = end($parts);

            $nextNumber = ((int)$lastNumber) + 1;
        }

        $nextNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$customerCode}/{$year}/{$nextNumber}";
    }

    function destroyExport(int $id)
    {
         try {
            DB::transaction(function () use ($id) {

                $branchId = (int) auth()->user()->branch_id;

                // Lock the sale
                $sale = Sale::lockForUpdate()->findOrFail($id);

                // Ensure the sale belongs to the logged-in user's branch
                if ((int) $sale->branch_id !== $branchId) {
                    abort(403, 'You are not allowed to cancel this sale.');
                }

                // Prevent cancelling an already-cancelled sale
                if ($sale->status === 'cancelled') {
                    throw new \Exception('This sale has already been cancelled.');
                }

                // Lock the sale items
                $saleItems = SaleItem::where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($saleItems as $saleItem) {

                    // Lock the relevant branch stock
                    $stock = BranchItemStock::where('branch_id', $branchId)
                        ->where('item_id', $saleItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception(
                            "Branch stock not found for item ID {$saleItem->item_id}."
                        );
                    }

                    // Restore sold quantity to branch stock.
                    // Do not change average_cost when cancelling a sale.
                    $stock->qty = (float) $stock->qty + (float) $saleItem->qty;
                    $stock->save();
                }

                // Reverse the accounting entries associated with this sale
                Accounting::reverse(Sale::class, $sale->id);

                // Mark the sale as cancelled
                $sale->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale cancelled successfully.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {

            // Preserve HTTP errors such as 403 and 404
            throw $e;
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Unable to cancel sale: ' . $e->getMessage()
            );
        }
    }

    public function invoicePdf(Request $request, $id)
    {
        $request->validate([
            'company_profile_id' => [
                'nullable',
                'integer',
                'exists:invoice_company_profiles,id',
            ],
        ]);

        $sale = Sale::with([
            'items.item',
            'customer'
        ])->findOrFail($id);


        /*
    |--------------------------------------------------------------------------
    | Branch Security
    |--------------------------------------------------------------------------
    */

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }


        /*
    |--------------------------------------------------------------------------
    | Company Profile
    |--------------------------------------------------------------------------
    */

        if ($request->filled('company_profile_id')) {

            $companyProfile = InvoiceCompanyProfile::where('status', true)
                ->findOrFail($request->company_profile_id);
        } else {

            $companyProfile = (object) [
                'title' => 'MR - ZAM HOLDINGS (PVT) LTD',
                'address' => '',
            ];
        }


        /*
    |--------------------------------------------------------------------------
    | Previous Sales
    |--------------------------------------------------------------------------
    */

        $previousSalesTotal = Sale::where('customer_id', $sale->customer_id)
            ->where('id', '<', $sale->id)
            ->sum('total');


        /*
    |--------------------------------------------------------------------------
    | Customer Payments
    |--------------------------------------------------------------------------
    */

        $customerPayments = SalesPayment::where(
            'customer_id',
            $sale->customer_id
        )->sum('amount');


        /*
    |--------------------------------------------------------------------------
    | Previous Payments
    |--------------------------------------------------------------------------
    */

        $previousPaymentsTotal = min(
            $customerPayments,
            $previousSalesTotal
        );


        /*
    |--------------------------------------------------------------------------
    | Previous Outstanding
    |--------------------------------------------------------------------------
    */

        $previousOutstanding = max(
            0,
            $previousSalesTotal - $previousPaymentsTotal
        );


        /*
    |--------------------------------------------------------------------------
    | Current Invoice
    |--------------------------------------------------------------------------
    */

        $currentInvoice = $sale->total;


        /*
    |--------------------------------------------------------------------------
    | Current Payment
    |--------------------------------------------------------------------------
    */

        $currentPayment = max(
            0,
            $customerPayments - $previousSalesTotal
        );

        $currentPayment = min(
            $currentPayment,
            $currentInvoice
        );


        /*
    |--------------------------------------------------------------------------
    | Current Balance
    |--------------------------------------------------------------------------
    */

        $currentBalance = max(
            0,
            $currentInvoice - $currentPayment
        );


        /*
    |--------------------------------------------------------------------------
    | Total Payable
    |--------------------------------------------------------------------------
    */

        $totalPayable =
            $previousOutstanding +
            $currentInvoice;


        /*
    |--------------------------------------------------------------------------
    | Group Same Items
    |--------------------------------------------------------------------------
    */

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {

                return (object) [
                    'item'       => $rows->first()->item,
                    'qty'        => $rows->sum('qty'),
                    'sale_price' => $rows->first()->sale_price,
                    'subtotal'   => $rows->sum('subtotal'),
                ];
            })
            ->values();

        /*
|--------------------------------------------------------------------------
| Group Parent + Child Items
|--------------------------------------------------------------------------
*/

        $groupedItems = $groupedItems
            ->groupBy(function ($row) {

                // Parent item
                if (is_null($row->item->parent_id)) {
                    return $row->item->id;
                }

                // Child item → group under parent
                return $row->item->parent_id;
            })
            ->map(function ($rows) {

                // Find the parent row
                $parentRow = $rows->first(function ($row) {
                    return is_null($row->item->parent_id);
                });

                /*
        |--------------------------------------------------------------------------
        | Parent exists
        |--------------------------------------------------------------------------
        */

                if ($parentRow) {

                    // Parent + all children weights
                    $totalWeight = $rows->sum('qty');

                    return (object) [
                        'item'       => $parentRow->item,
                        'qty'        => $rows->sum('qty'),


                        // Parent rate
                        'sale_price' => $parentRow->sale_price,

                        // Parent rate × combined weight
                        'subtotal'   => $totalWeight * $parentRow->sale_price,
                    ];
                }

                /*
        |--------------------------------------------------------------------------
        | Child exists without parent in this sale
        |--------------------------------------------------------------------------
        */

                $first = $rows->first();

                return (object) [
                    'item'       => $first->item,
                    'qty'        => $rows->sum('qty'),
                    'sale_price' => $first->sale_price,
                    'subtotal'   => $rows->sum('subtotal'),
                ];
            })
            ->values();


        $invoiceCompanyProfiles = InvoiceCompanyProfile::where('status', true)->get();

        /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */

        $pdf = Pdf::loadView('sales.invoice-pdf', compact(
            'sale',
            'groupedItems',
            'previousOutstanding',
            'currentInvoice',
            'currentPayment',
            'currentBalance',
            'totalPayable',
            'companyProfile'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream(
            'Invoice-' . $sale->id . '.pdf'
        );
    }

    public function invoiceExportold($id)
    {
        $sale = Sale::with([
            'customer',
            'items.item'
        ])->findOrFail($id);

        $invoiceCompanyProfiles = InvoiceCompanyProfile::where('status', true)
            ->orderBy('title')
            ->get();

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price_foreign' => $rows->sum('sub_total_foreign') / max($rows->sum('qty'), 1),
                    'sub_total_foreign' => $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();

        /*
|--------------------------------------------------------------------------
| Group Parent + Child Items
|--------------------------------------------------------------------------
*/

        $groupedItems = $groupedItems
            ->groupBy(function ($row) {

                // Parent item
                if (is_null($row->item->parent_id)) {
                    return $row->item->id;
                }

                // Child item → group under parent
                return $row->item->parent_id;
            })
            ->map(function ($rows) {

                // Find parent item
                $parentRow = $rows->first(function ($row) {
                    return is_null($row->item->parent_id);
                });

                if ($parentRow) {

                    // Parent + all children quantity
                    $totalQty = $rows->sum('qty');

                    // Use parent's foreign rate
                    $parentRate = $parentRow->sale_price_foreign;

                    return (object) [
                        'item' => $parentRow->item,
                        'qty' => $totalQty,
                        'sale_price_foreign' => $parentRate,
                        'sub_total_foreign' => $totalQty * $parentRate,
                    ];
                }

                /*
        |--------------------------------------------------------------------------
        | Child without parent in this sale
        |--------------------------------------------------------------------------
        */

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price_foreign' => $first->sale_price_foreign,
                    'sub_total_foreign' => $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();



        /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */

        $pdf = Pdf::loadView('sales.invoice-pdf', compact(
            'sale',
            'groupedItems',
            'previousOutstanding',
            'currentInvoice',
            'currentPayment',
            'currentBalance',
            'totalPayable',
            'companyProfile'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream(
            'Invoice-' . $sale->id . '.pdf'
        );
    }

    public function invoiceExportPdf(Request $request, $id)
    {
        $request->validate([
            'company_profile_id' => [
                'nullable',
                'integer',
                'exists:invoice_company_profiles,id',
            ],
        ]);


        /*
    |--------------------------------------------------------------------------
    | Sale
    |--------------------------------------------------------------------------
    */

        $sale = Sale::with([
            'customer',
            'items.item'
        ])->findOrFail($id);


        /*
    |--------------------------------------------------------------------------
    | Branch Security
    |--------------------------------------------------------------------------
    */

        if ((int) $sale->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to view this sale.');
        }


        /*
    |--------------------------------------------------------------------------
    | Company Profile
    |--------------------------------------------------------------------------
    */

        if ($request->filled('company_profile_id')) {

            $companyProfile = InvoiceCompanyProfile::where('status', true)
                ->findOrFail($request->company_profile_id);
        } else {

            $companyProfile = (object) [
                'title' => 'MR - ZAM HOLDINGS (PVT) LTD',
                'address' => '',
            ];
        }


        /*
    |--------------------------------------------------------------------------
    | Group Same Items
    |--------------------------------------------------------------------------
    */

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,

                    'qty' => $rows->sum('qty'),

                    'sale_price_foreign' =>
                    $rows->sum('sub_total_foreign')
                        / max($rows->sum('qty'), 1),

                    'sub_total_foreign' =>
                    $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();

        /*
|--------------------------------------------------------------------------
| Group Parent + Child Items
|--------------------------------------------------------------------------
*/

        $groupedItems = $groupedItems
            ->groupBy(function ($row) {

                // Parent item
                if (is_null($row->item->parent_id)) {
                    return $row->item->id;
                }

                // Child item → group under parent
                return $row->item->parent_id;
            })
            ->map(function ($rows) {

                // Find parent item
                $parentRow = $rows->first(function ($row) {
                    return is_null($row->item->parent_id);
                });

                if ($parentRow) {

                    // Parent + all children quantity
                    $totalQty = $rows->sum('qty');

                    // Use parent's foreign rate
                    $parentRate = $parentRow->sale_price_foreign;

                    return (object) [
                        'item' => $parentRow->item,
                        'qty' => $totalQty,
                        'sale_price_foreign' => $parentRate,
                        'sub_total_foreign' => $totalQty * $parentRate,
                    ];
                }

                /*
        |--------------------------------------------------------------------------
        | Child without parent in this sale
        |--------------------------------------------------------------------------
        */

                $first = $rows->first();

                return (object) [
                    'item' => $first->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price_foreign' => $first->sale_price_foreign,
                    'sub_total_foreign' => $rows->sum('sub_total_foreign'),
                ];
            })
            ->values();



        /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */

        $pdf = Pdf::loadView(
            'sales.export-invoice-pdf',
            compact(
                'sale',
                'groupedItems',
                'companyProfile'
            )
        );


        $pdf->setPaper('A4', 'portrait');


        return $pdf->stream(
            'Export-Invoice-' . $sale->id . '.pdf'
        );
    }
}
