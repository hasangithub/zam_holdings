<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{

    public function index()
    {
        $sales = Sale::where('currency', 'LKR')->with('customer')->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function indexExport()
    {
        $sales = Sale::where('currency', 'USD')
            ->latest()
            ->get();
        return view('sales.index_export', compact('sales'));
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

    public function createExport()
    {
        $customers = Customer::where('customer_type', 'export')->get();

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

        return view('sales.export_create', compact('customers', 'stocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',

            'items' => 'required|array|min:1',

            'items.*.group_key' => 'required',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
        ]);

        try {

            $sale = DB::transaction(function () use ($request) {

                $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);

                $sale = Sale::create([
                    'customer_id' => $customer->id,
                    'sale_date' => now(),
                    'currency' => 'LKR',
                    'exchange_rate' => 1,
                    'total' => 0,
                    'total_foreign' => 0,
                    'invoice_id' =>
                    $this->generateInvoiceId(
                        $customer->name
                    ),
                ]);

                $total = 0;
                $costOfGoodsSold = 0;

                foreach ($request->items as $row) {

                    [$itemId, $price] = explode('|', $row['group_key']);

                    $itemId = (int) $itemId;
                    $price = (float) $price;
                    $qtyNeeded = (float) $row['qty'];

                    $batches = PurchaseItem::where('item_id', $itemId)->where('price', $price)->where('remaining_qty', '>', 0)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $availableQty = $batches->sum(
                        fn($batch) =>
                        (float) $batch->remaining_qty
                    );


                    if ($availableQty < $qtyNeeded) {

                        throw ValidationException::withMessages([

                            'items' =>
                            "Not enough stock for item ID {$itemId}. "
                                . "Available: "
                                . number_format(
                                    $availableQty,
                                    3
                                )
                                . ", Required: "
                                . number_format(
                                    $qtyNeeded,
                                    3
                                ),

                        ]);
                    }
                    $remainingToDeduct = $qtyNeeded;

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }


                        $deduct = min(
                            (float) $batch->remaining_qty,
                            $remainingToDeduct
                        );

                        $costOfGoodsSold += $deduct * (float) $batch->price;

                        $batch->decrement(
                            'remaining_qty',
                            $deduct
                        );


                        $remainingToDeduct -= $deduct;
                    }

                    $salePrice =
                        isset($row['sale_price'])
                        ? (float) $row['sale_price']
                        : $price;


                    $subtotal =
                        $qtyNeeded * $salePrice;

                    SaleItem::create([

                        'sale_id' => $sale->id,
                        'item_id' => $itemId,
                        'qty' => $qtyNeeded,
                        'sale_price' => $salePrice,
                        'base_price' => $price,
                        'subtotal' => $subtotal,
                        'sale_price_foreign' => 0,
                        'sub_total_foreign' => 0,
                    ]);

                    $total += $subtotal;
                }

                $sale->update([
                    'total' => $total,
                    'total_foreign' => 0,
                ]);

                Accounting::postJournal([

                    'branch_id' => auth()->user()->branch_id,
                    'date' => $sale->sale_date,
                    'description' =>
                    'Credit Sale - Invoice ' . $sale->invoice_id,

                    'entries' => [

                        /*
                    |--------------------------------------------------------------------------
                    | Customer Receivable
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' => 3, // Accounts Receivable
                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                            'debit' => $total,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 6, // Sales
                            'sub_ledger_id' => null,
                            'debit' => 0,
                            'credit' => $total,
                        ],

                        [
                            'ledger_id' => 8, // COGS ledger
                            'sub_ledger_id' => null,
                            'debit' => $costOfGoodsSold,
                            'credit' => 0,
                        ],

                        // Inventory
                        [
                            'ledger_id' => 4, // Inventory ledger
                            'sub_ledger_id' => 1, // Inventory subledger
                            'debit' => 0,
                            'credit' => $costOfGoodsSold,
                        ],
                    ],

                ]);

                return $sale;
            });


            return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            return back()->withInput()->with('error', 'Unable to create sale. Please try again.');
        }
    }

    public function storeExport(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'exchange_rate' => 'required|numeric|gt:0',

            'items' => 'required|array|min:1',

            'items.*.group_key' => 'required',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.sale_price_foreign' =>
            'required|numeric|min:0',
        ]);

        try {

            $sale = DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;

                $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);

                $exchangeRate = (float) $request->exchange_rate;

                $sale = Sale::create([

                    'branch_id' =>
                    $branchId,

                    'customer_id' =>
                    $customer->id,

                    'sale_date' =>
                    now(),

                    'currency' =>
                    'USD',

                    'exchange_rate' =>
                    $exchangeRate,

                    'total' =>
                    0,

                    'total_foreign' =>
                    0,

                    'invoice_id' =>
                    $this->generateInvoiceId(
                        $customer->customer_code
                    ),

                    'consignor' =>
                    $request->consignor,

                    'consignee_name' =>
                    $request->consignee_name,

                    'consignee_address' =>
                    $request->consignee_address,

                    'port_of_loading' =>
                    $request->port_of_loading,

                    'country_of_orgin' =>
                    $request->country_of_orgin,

                    'mode_of_payment' =>
                    $request->mode_of_payment,

                    'mode_of_shipping' =>
                    $request->mode_of_shipping,

                    'flight_no' =>
                    $request->flight_no,

                ]);


                $totalUsd = 0;
                $totalLkr = 0;
                $totalCogs = 0;

                foreach ($request->items as $row) {

                    [$itemId, $price, $stock] =
                        explode('|', $row['group_key']);


                    $itemId = (int) $itemId;

                    $quantity = (float) $row['qty'];

                    $remainingToDeduct = $quantity;

                    $batches = PurchaseItem::where(
                        'item_id',
                        $itemId
                    )
                        ->where(
                            'remaining_qty',
                            '>',
                            0
                        )
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();


                    $availableQty = $batches->sum(
                        fn($batch) =>
                        (float) $batch->remaining_qty
                    );


                    if ($availableQty < $quantity) {

                        throw ValidationException::withMessages([

                            'items' =>
                            "Not enough stock for item ID "
                                . $itemId
                                . ". Available: "
                                . number_format(
                                    $availableQty,
                                    3
                                )
                                . ", Required: "
                                . number_format(
                                    $quantity,
                                    3
                                ),

                        ]);
                    }


                    /*
                |--------------------------------------------------------------------------
                | FIFO Deduction + COGS
                |--------------------------------------------------------------------------
                */

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }


                        $deduct = min(
                            (float) $batch->remaining_qty,
                            $remainingToDeduct
                        );


                        /*
                    |--------------------------------------------------------------------------
                    | COGS
                    |--------------------------------------------------------------------------
                    */

                        $totalCogs +=
                            $deduct *
                            (float) $batch->price;


                        /*
                    |--------------------------------------------------------------------------
                    | Reduce Stock
                    |--------------------------------------------------------------------------
                    */

                        $batch->decrement(
                            'remaining_qty',
                            $deduct
                        );


                        $remainingToDeduct -= $deduct;
                    }


                    /*
                |--------------------------------------------------------------------------
                | USD Selling Price
                |--------------------------------------------------------------------------
                */

                    $salePriceUsd =
                        (float) $row['sale_price_foreign'];


                    $subtotalUsd =
                        $quantity *
                        $salePriceUsd;


                    /*
                |--------------------------------------------------------------------------
                | Convert USD → LKR
                |--------------------------------------------------------------------------
                */

                    $subtotalLkr =
                        $subtotalUsd *
                        $exchangeRate;


                    /*
                |--------------------------------------------------------------------------
                | Create Sale Item
                |--------------------------------------------------------------------------
                */

                    SaleItem::create([

                        'sale_id' =>
                        $sale->id,

                        'item_id' =>
                        $itemId,

                        'qty' =>
                        $quantity,

                        'base_price' =>
                        $price,

                        'sale_price_foreign' =>
                        $salePriceUsd,

                        'sub_total_foreign' =>
                        $subtotalUsd,

                        'sale_price' =>
                        $salePriceUsd *
                            $exchangeRate,

                        'subtotal' =>
                        $subtotalLkr,

                    ]);


                    $totalUsd += $subtotalUsd;

                    $totalLkr += $subtotalLkr;
                }


                /*
            |--------------------------------------------------------------------------
            | Update Sale Totals
            |--------------------------------------------------------------------------
            */

                $sale->update([

                    'total_foreign' =>
                    $totalUsd,

                    'total' =>
                    $totalLkr,

                ]);


                /*
            |--------------------------------------------------------------------------
            | Export Sale Accounting
            |--------------------------------------------------------------------------
            |
            | Accounting is maintained in LKR.
            |
            */

                Accounting::postJournal([

                    'branch_id' =>
                    $branchId,

                    'date' =>
                    $sale->sale_date,

                    'description' =>
                    'Export Sale - Invoice '
                        . $sale->invoice_id,

                    'entries' => [

                        /*
                    |--------------------------------------------------------------------------
                    | Customer Receivable
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' => 3, // Accounts Receivable

                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,

                            'debit' =>
                            $totalLkr,

                            'credit' =>
                            0,
                        ],

                        /*
                    |--------------------------------------------------------------------------
                    | Sales Revenue
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            7, // Sales Revenue

                            'sub_ledger_id' =>
                            null,

                            'debit' =>
                            0,

                            'credit' =>
                            $totalLkr,
                        ],

                        /*
                    |--------------------------------------------------------------------------
                    | COGS
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            9, // Cost of Goods Sold

                            'sub_ledger_id' =>
                            null,

                            'debit' =>
                            $totalCogs,

                            'credit' =>
                            0,
                        ],

                        /*
                    |--------------------------------------------------------------------------
                    | Inventory
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            4, // Inventory

                            'sub_ledger_id' =>
                            1, // Inventory sub-ledger

                            'debit' =>
                            0,

                            'credit' =>
                            $totalCogs,
                        ],

                    ],

                ]);


                return $sale;
            });


            return redirect()
                ->route('export-sales.index')
                ->with(
                    'success',
                    'Export sale created successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            \Log::error(
                'Export sale creation failed',
                [
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                ]
            );


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create export sale.'
                );
        }
    }

    public function show(int $id)
    {
        $sale = Sale::with(['items'])->findOrFail($id);

        return view('sales.show', compact('sale'));
    }

    public function invoice($id)
    {
        $sale = Sale::with(['items.item'])->findOrFail($id);

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {
                return (object)[
                    'item' => $rows->first()->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price' => $rows->first()->sale_price,
                    'subtotal' => $rows->sum('subtotal'),
                ];
            })
            ->values();

        return view('sales.invoice', compact('sale', 'groupedItems'));
    }

    public function invoiceExport($id)
    {
        $sale = Sale::with(['items.item'])->findOrFail($id);

        $groupedItems = $sale->items
            ->groupBy('item_id')
            ->map(function ($rows) {
                return (object)[
                    'item' => $rows->first()->item,
                    'qty' => $rows->sum('qty'),
                    'sale_price' => $rows->first()->sale_price,
                    'subtotal' => $rows->sum('subtotal'),
                ];
            })
            ->values();

        return view('sales.export_invoice', compact('sale', 'groupedItems'));
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

    public function editExport($id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        return view('sales.export_edit', [
            'sale' => $sale,
            'customers' => Customer::all(),
            'items' => Item::all(),
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
            'total' => $total,
            'balance_amount' => $total - $sale->total_paid,
        ]);

        return redirect()->route('sales.index');
    }

    public function updateExport(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        // delete old items
        $sale->items()->delete();

        $totalLkr = 0;
        $totalUsd = 0;

        foreach ($request->items as $row) {

            $qty = $row['qty'];
            $itemId = $row['item_id'];
            $basePrice = $row['base_price'];

            // USD price from form
            $salePriceUsd = $row['sale_price_foreign'];

            // calculations
            $subtotalUsd = $qty * $salePriceUsd;
            $subtotalLkr = $subtotalUsd * $sale->exchange_rate;

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $itemId,
                'qty' => $qty,
                'base_price' => $basePrice,

                // USD fields
                'sale_price_foreign' => $salePriceUsd,
                'sub_total_foreign'  => $subtotalUsd,

                // LKR fields (for reporting compatibility)
                'sale_price' => $subtotalLkr / $qty,
                'subtotal' => $subtotalLkr,
            ]);

            $totalUsd += $subtotalUsd;
            $totalLkr += $subtotalLkr;
        }

        $sale->update([
            'customer_id' => $request->customer_id,
            'total_foreign' => $totalUsd,
            'total' => $totalLkr,
            'balance_amount' => $totalLkr - $sale->total_paid,
        ]);

        return redirect()->route('sales.index');
    }

    // DELETE
    public function destroy($id)
    {
        Sale::findOrFail($id)->delete();
        return back();
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
}
