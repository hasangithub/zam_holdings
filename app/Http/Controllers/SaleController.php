<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\PurchaseItem;
use App\Models\SalesPayment;
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
        $customers = Customer::where('customer_type', 'local')->get();

        /*
    |--------------------------------------------------------------------------
    | POS STOCK
    |--------------------------------------------------------------------------
    | Group same item + same cost price.
    |
    | Example:
    |
    | Item A / 100 / 10
    | Item A / 200 / 20
    | Item A / 100 / 30
    |
    | POS shows:
    |
    | Item A / 100 / 40
    | Item A / 200 / 20
    |
    | But actual purchase_items remain separate.
    |--------------------------------------------------------------------------
    */

        $stocks = DB::table('purchase_items')
            ->join(
                'items',
                'items.id',
                '=',
                'purchase_items.item_id'
            )

            ->select(
                'purchase_items.item_id',
                'items.name as item_name',
                'purchase_items.price',

                DB::raw(
                    'SUM(purchase_items.remaining_qty) as total_qty'
                )
            )

            ->where(
                'purchase_items.remaining_qty',
                '>',
                0
            )

            ->groupBy(
                'purchase_items.item_id',
                'purchase_items.price',
                'items.name'
            )

            ->orderBy('items.name')
            ->orderBy('purchase_items.price')

            ->get();


        return view(
            'sales.create',
            compact(
                'customers',
                'stocks'
            )
        );
    }

    public function createExport()
    {
        $customers = Customer::where('customer_type', 'export')->get();

        $stocks = DB::table('purchase_items')
            ->join(
                'items',
                'items.id',
                '=',
                'purchase_items.item_id'
            )
            ->select(
                'purchase_items.item_id',
                'items.name as item_name',
                'purchase_items.price',
                DB::raw('SUM(purchase_items.remaining_qty) as total_qty')
            )
            ->where(
                'purchase_items.remaining_qty',
                '>',
                0
            )
            ->groupBy(
                'purchase_items.item_id',
                'purchase_items.price',
                'items.name'
            )
            ->orderBy('items.name')
            ->orderBy('purchase_items.price')
            ->get();

        return view(
            'sales.export_create',
            compact('customers', 'stocks')
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'customer_id' =>
            'required|exists:customers,id',

            'items' =>
            'required|array|min:1',

            'items.*.group_key' =>
            'required',

            'items.*.qty' =>
            'required|numeric|gt:0',

            'items.*.sale_price' =>
            'required|numeric|min:0',

        ]);


        try {

            $sale = DB::transaction(function () use ($request) {

                $branchId =
                    auth()->user()->branch_id;


                /*
            |--------------------------------------------------------------------------
            | LOCK CUSTOMER
            |--------------------------------------------------------------------------
            */

                $customer =
                    Customer::lockForUpdate()
                    ->findOrFail(
                        $request->customer_id
                    );


                /*
            |--------------------------------------------------------------------------
            | CREATE SALE HEADER
            |--------------------------------------------------------------------------
            */

                $sale = Sale::create([

                    'branch_id' =>
                    $branchId,

                    'customer_id' =>
                    $customer->id,

                    'sale_date' =>
                    now(),

                    'currency' =>
                    'LKR',

                    'exchange_rate' =>
                    1,

                    'total' =>
                    0,

                    'total_foreign' =>
                    0,

                    'invoice_id' =>
                    $this->generateInvoiceId(
                        $customer->name
                    ),

                ]);


                $total = 0;

                $costOfGoodsSold = 0;


                /*
            |--------------------------------------------------------------------------
            | PROCESS POS ITEMS
            |--------------------------------------------------------------------------
            */

                foreach ($request->items as $row) {


                    /*
                |--------------------------------------------------------------------------
                | group_key
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | 1|100
                |
                */

                    $parts =
                        explode(
                            '|',
                            $row['group_key']
                        );


                    if (count($parts) < 2) {

                        throw ValidationException::withMessages([

                            'items' =>
                            'Invalid item selection.'

                        ]);
                    }


                    $itemId =
                        (int) $parts[0];


                    $costPrice =
                        (float) $parts[1];


                    $qtyRequested =
                        (float) $row['qty'];


                    $salePrice =
                        (float) $row['sale_price'];


                    /*
                |--------------------------------------------------------------------------
                | LOCK ALL MATCHING PURCHASE BATCHES
                |--------------------------------------------------------------------------
                */

                    $batches =
                        PurchaseItem::query()

                        ->where(
                            'item_id',
                            $itemId
                        )

                        ->where(
                            'price',
                            $costPrice
                        )

                        ->where(
                            'remaining_qty',
                            '>',
                            0
                        )

                        /*
                        | FIFO
                        */
                        ->orderBy('id')

                        /*
                        | Prevent another sale from using
                        | these rows at the same time.
                        */
                        ->lockForUpdate()

                        ->get();


                    /*
                |--------------------------------------------------------------------------
                | CALCULATE AVAILABLE STOCK
                |--------------------------------------------------------------------------
                */

                    $availableQty =
                        $batches->sum(
                            function ($batch) {

                                return (float)
                                $batch->remaining_qty;
                            }
                        );


                    /*
                |--------------------------------------------------------------------------
                | STOCK CHECK
                |--------------------------------------------------------------------------
                */

                    if (
                        $availableQty <
                        $qtyRequested
                    ) {

                        $itemName =
                            optional(
                                Item::find($itemId)
                            )->name
                            ?? "Item ID {$itemId}";


                        throw ValidationException::withMessages([

                            'items' =>
                            "Not enough stock for {$itemName} "
                                . "at cost price "
                                . number_format(
                                    $costPrice,
                                    2
                                )
                                . ". Available: "
                                . number_format(
                                    $availableQty,
                                    3
                                )
                                . ", Required: "
                                . number_format(
                                    $qtyRequested,
                                    3
                                ),

                        ]);
                    }


                    /*
                |--------------------------------------------------------------------------
                | KEEP ORIGINAL QUANTITY
                |--------------------------------------------------------------------------
                */

                    $remainingToDeduct =
                        $qtyRequested;


                    /*
                |--------------------------------------------------------------------------
                | DEDUCT FROM PURCHASE BATCHES
                |--------------------------------------------------------------------------
                */

                    foreach ($batches as $batch) {

                        if (
                            $remainingToDeduct <= 0
                        ) {

                            break;
                        }


                        $batchRemaining =
                            (float)
                            $batch->remaining_qty;


                        $deductQty =
                            min(
                                $batchRemaining,
                                $remainingToDeduct
                            );


                        /*
                    |--------------------------------------------------------------------------
                    | SALE SUBTOTAL FOR THIS BATCH
                    |--------------------------------------------------------------------------
                    */

                        $saleSubtotal =
                            $deductQty *
                            $salePrice;


                        /*
                    |--------------------------------------------------------------------------
                    | COGS FOR THIS BATCH
                    |--------------------------------------------------------------------------
                    */

                        $batchCost =
                            $deductQty *
                            (float) $batch->price;


                        /*
                    |--------------------------------------------------------------------------
                    | UPDATE PURCHASE STOCK
                    |--------------------------------------------------------------------------
                    */

                        $batch->remaining_qty =
                            $batchRemaining -
                            $deductQty;

                        $batch->save();


                        /*
                    |--------------------------------------------------------------------------
                    | CREATE SALE ITEM
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | One sale can create multiple sale_items
                    | for the same product when multiple
                    | purchase batches are used.
                    |
                    |--------------------------------------------------------------------------
                    */

                        SaleItem::create([

                            'sale_id' =>
                            $sale->id,

                            'item_id' =>
                            $itemId,

                            'purchase_item_id' =>
                            $batch->id,

                            'qty' =>
                            $deductQty,

                            'sale_price' =>
                            $salePrice,

                            'base_price' =>
                            $batch->price,

                            'subtotal' =>
                            $saleSubtotal,

                            'sale_price_foreign' =>
                            0,

                            'sub_total_foreign' =>
                            0,

                        ]);


                        /*
                    |--------------------------------------------------------------------------
                    | TOTALS
                    |--------------------------------------------------------------------------
                    */

                        $total +=
                            $saleSubtotal;


                        $costOfGoodsSold +=
                            $batchCost;


                        $remainingToDeduct -=
                            $deductQty;
                    }


                    /*
                |--------------------------------------------------------------------------
                | SAFETY CHECK
                |--------------------------------------------------------------------------
                */

                    if (
                        $remainingToDeduct > 0
                    ) {

                        throw ValidationException::withMessages([

                            'items' =>
                            'Stock deduction failed.'

                        ]);
                    }
                }


                /*
            |--------------------------------------------------------------------------
            | UPDATE SALE TOTAL
            |--------------------------------------------------------------------------
            */

                $sale->update([

                    'total' =>
                    $total,

                    'total_foreign' =>
                    0,

                    'balance_amount' =>
                    $total,

                ]);


                /*
            |--------------------------------------------------------------------------
            | SALES + COGS JOURNAL
            |--------------------------------------------------------------------------
            |
            | Dr Customer Receivable
            |     Cr Sales
            |
            | Dr COGS
            |     Cr Inventory
            |--------------------------------------------------------------------------
            */

                Accounting::postJournal([

                    'branch_id' =>
                    $branchId,

                    'date' =>
                    $sale->sale_date,

                    'description' =>
                    'Credit Sale - Invoice '
                        . $sale->invoice_id,

                    'entries' => [

                        /*
                    |--------------------------------------------------------------------------
                    | CUSTOMER RECEIVABLE
                    |--------------------------------------------------------------------------
                    */

                        [

                            'ledger_id' =>
                            3,

                            'sub_ledger_id' =>
                            $customer
                                ->receivable_sub_ledger_id,

                            'debit' =>
                            $total,

                            'credit' =>
                            0,

                        ],


                        /*
                    |--------------------------------------------------------------------------
                    | SALES
                    |--------------------------------------------------------------------------
                    */

                        [

                            'ledger_id' =>
                            6,

                            'sub_ledger_id' =>
                            null,

                            'debit' =>
                            0,

                            'credit' =>
                            $total,

                        ],


                        /*
                    |--------------------------------------------------------------------------
                    | COGS
                    |--------------------------------------------------------------------------
                    */

                        [

                            'ledger_id' =>
                            8,

                            'sub_ledger_id' =>
                            null,

                            'debit' =>
                            $costOfGoodsSold,

                            'credit' =>
                            0,

                        ],


                        /*
                    |--------------------------------------------------------------------------
                    | INVENTORY
                    |--------------------------------------------------------------------------
                    */

                        [

                            'ledger_id' =>
                            4,

                            'sub_ledger_id' =>
                            1,

                            'debit' =>
                            0,

                            'credit' =>
                            $costOfGoodsSold,

                        ],

                    ],

                ]);


                return $sale;
            });


            return redirect()
                ->route('sales.index')
                ->with(
                    'success',
                    'Sale created successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors(
                    $e->errors()
                )
                ->withInput();
        } catch (\Throwable $e) {

            \Log::error(
                'Local sale creation failed',
                [

                    'user_id' =>
                    auth()->id(),

                    'error' =>
                    $e->getMessage(),

                    'trace' =>
                    $e->getTraceAsString(),

                ]
            );


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create sale. Please try again.'
                );
        }
    }

    public function storeExport(Request $request)
    {
        $request->validate([

            'customer_id' =>
            'required|exists:customers,id',

            'exchange_rate' =>
            'required|numeric|gt:0',

            'items' =>
            'required|array|min:1',

            'items.*.group_key' =>
            'required',

            'items.*.qty' =>
            'required|numeric|gt:0',

            'items.*.sale_price_foreign' =>
            'required|numeric|min:0',

        ]);

        try {

            $sale = DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;
                $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);
                $exchangeRate =  (float) $request->exchange_rate;

                $sale = Sale::create([

                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'sale_date' => now(),
                    'currency' => 'USD',
                    'exchange_rate' => $exchangeRate,
                    'total' => 0,
                    'total_foreign' => 0,
                    'invoice_id' => $this->generateInvoiceId($customer->customer_code),
                    'consignor' => $request->consignor,
                    'consignee_name' => $customer->consignee_name,
                    'consignee_address' => $customer->consignee_address,
                    'port_of_loading' => $request->port_of_loading,
                    'country_of_orgin' => $request->country_of_orgin,
                    'mode_of_payment' => $request->mode_of_payment,
                    'mode_of_shipping' => $request->mode_of_shipping,
                    'flight_no' => $request->flight_no,
                ]);

                $totalUsd = 0;
                $totalLkr = 0;
                $totalCogs = 0;

                foreach ($request->items as $row) {

                    $parts = explode('|', $row['group_key']);

                    if (count($parts) < 2) {
                        throw ValidationException::withMessages(['items' => 'Invalid stock selection.']);
                    }

                    $itemId    = (int) $parts[0];
                    $costPrice = (float) $parts[1];
                    $quantity  = (float) $row['qty'];

                    $batches = PurchaseItem::where('item_id', $itemId)
                        ->where('price', $costPrice)
                        ->where('remaining_qty', '>', 0)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $availableQty = $batches->sum(fn($batch) => (float) $batch->remaining_qty);

                    if ($availableQty < $quantity) {

                        throw ValidationException::withMessages([
                            'items' =>
                            "Not enough stock for the selected cost price. "
                                . "Item ID: {$itemId}, "
                                . "Cost: "
                                . number_format(
                                    $costPrice,
                                    2
                                )
                                . ", Available: "
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

                    $remainingToDeduct = $quantity;

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $deduct = min((float) $batch->remaining_qty, $remainingToDeduct);
                        $totalCogs += $deduct * (float) $batch->price;

                        $batch->remaining_qty = (float) $batch->remaining_qty - $deduct;
                        $batch->save();

                        $remainingToDeduct -= $deduct;
                    }

                    if ($remainingToDeduct > 0) {

                        throw ValidationException::withMessages([

                            'items' =>
                            'Stock changed while processing the sale. '
                                . 'Please try again.'

                        ]);
                    }

                    $salePriceUsd = (float) $row['sale_price_foreign'];
                    $subtotalUsd = $quantity * $salePriceUsd;
                    $subtotalLkr = $subtotalUsd * $exchangeRate;

                    SaleItem::create([

                        'sale_id' => $sale->id,
                        'item_id' => $itemId,
                        'qty' => $quantity,
                        'base_price' => $costPrice,
                        'sale_price_foreign' => $salePriceUsd,
                        'sub_total_foreign' => $subtotalUsd,
                        'sale_price' => $salePriceUsd * $exchangeRate,
                        'subtotal' => $subtotalLkr,
                    ]);

                    $totalUsd += $subtotalUsd;
                    $totalLkr += $subtotalLkr;
                }

                $sale->update([
                    'total_foreign' => $totalUsd,
                    'total' => $totalLkr,
                ]);


                /*
            |--------------------------------------------------------------------------
            | ACCOUNTING
            |--------------------------------------------------------------------------
            |
            | Dr Customer Receivable
            |     Cr Sales Revenue
            |
            | Dr COGS
            |     Cr Inventory
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
                    | CUSTOMER RECEIVABLE
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            3,

                            'sub_ledger_id' =>
                            $customer
                                ->receivable_sub_ledger_id,

                            'debit' =>
                            $totalLkr,

                            'credit' =>
                            0,
                        ],


                        /*
                    |--------------------------------------------------------------------------
                    | SALES REVENUE
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            7,

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
                            9,

                            'sub_ledger_id' =>
                            null,

                            'debit' =>
                            $totalCogs,

                            'credit' =>
                            0,
                        ],


                        /*
                    |--------------------------------------------------------------------------
                    | INVENTORY
                    |--------------------------------------------------------------------------
                    */

                        [
                            'ledger_id' =>
                            4,

                            'sub_ledger_id' =>
                            1,

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
            dd($e);
            return back()
                ->withErrors(
                    $e->errors()
                )
                ->withInput();
        } catch (\Throwable $e) {

            dd($e);
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create export sale. Please try again.'
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
    $sale = Sale::with(['items.item', 'customer'])
        ->findOrFail($id);

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
    | Previous Payments
    |--------------------------------------------------------------------------
    */

    $previousPaymentsTotal = SalesPayment::where(
        'customer_id',
        $sale->customer_id
    )
        ->where('id', '<', function ($query) use ($sale) {
            $query->selectRaw('COALESCE(MAX(id), 0)')
                ->from('sales_payments')
                ->where('customer_id', $sale->customer_id);
        })
        ->sum('amount');


    /*
    |--------------------------------------------------------------------------
    | Previous Outstanding
    |--------------------------------------------------------------------------
    */

    $previousOutstanding =
        $previousSalesTotal - $previousPaymentsTotal;


    /*
    |--------------------------------------------------------------------------
    | Group Same Items
    |--------------------------------------------------------------------------
    */

    $groupedItems = $sale->items
        ->groupBy('item_id')
        ->map(function ($rows) {

            return (object) [
                'item' => $rows->first()->item,
                'qty' => $rows->sum('qty'),
                'sale_price' => $rows->first()->sale_price,
                'subtotal' => $rows->sum('subtotal'),
            ];

        })
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Current Invoice + Previous Outstanding
    |--------------------------------------------------------------------------
    */

    $currentInvoice = $sale->total;

    $totalPayable =
        $previousOutstanding +
        $currentInvoice;


    return view('sales.invoice', compact(
        'sale',
        'groupedItems',
        'previousOutstanding',
        'currentInvoice',
        'totalPayable'
    ));
}

    public function invoiceExport($id)
    {
        $sale = Sale::with([
            'customer',
            'items.item'
        ])->findOrFail($id);

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

        return view(
            'sales.export_invoice',
            compact('sale', 'groupedItems')
        );
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
