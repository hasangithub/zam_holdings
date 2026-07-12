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

        return view('sales.export_create', compact('customers', 'stocks'));
    }

    public function store(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);

        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'sale_date' => now(),
            'currency'       => 'LKR',
            'exchange_rate'  => 1,
            'total'          => 0,
            'total_foreign'  => 0,
            'invoice_id' =>  $this->generateInvoiceId($customer->name),
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

            // dd($batches);

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
                'sale_price_foreign' => 0,
                'sub_total_foreign'  => 0,
            ]);

            $total += $subtotal;
        }

        $sale->update(['total' => $total, 'balance_amount' => $total, 'total_foreign'  => 0]);

        return redirect()->route('sales.index');
    }

    public function storeExport(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);

        $sale = Sale::create([
            'customer_id'    => $request->customer_id,
            'sale_date'      => now(),
            'currency'       => 'USD',
            'exchange_rate'  => $request->exchange_rate,
            'total'          => 0,
            'total_foreign'  => 0,
            'total_paid'     => 0,
            'balance_amount' => 0,
            'invoice_id'     => $this->generateInvoiceId($customer->customer_code),
            'consignor'      => $request->consignor,
            'consignee_name'    => $request->port_of_loading,
            'consignee_address' => $request->port_of_loading,
            'port_of_loading'   => $request->port_of_loading,
            'country_of_orgin'  => $request->country_of_orgin,
            'mode_of_payment'   => $request->mode_of_payment,
            'mode_of_shipping'  => $request->mode_of_shipping,
            'flight_no'          => $request->flight_no,
        ]);

        $totalLkr = 0;
        $totalUsd = 0;

        foreach ($request->items as $row) {

            list($item_id, $price, $stock) = explode('|', $row['group_key']);

            $qtyNeeded = $row['qty'];

            // =========================
            // STOCK DEDUCTION (UNCHANGED)
            // =========================
            $batches = PurchaseItem::where('item_id', $item_id)
                ->where('price', $price)
                ->where('remaining_qty', '>', 0)
                ->orderBy('id')
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

            // =========================
            // EXPORT PRICING (USD)
            // =========================
            $salePriceUsd = $row['sale_price_foreign'];

            $subtotalUsd = $row['qty'] * $salePriceUsd;
            $subtotalLkr = $subtotalUsd * $sale->exchange_rate;

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $item_id,

                'qty' => $row['qty'],

                // LKR base reference (same as local system)
                'base_price' => $price,

                // USD values
                'sale_price_foreign' => $salePriceUsd,
                'sub_total_foreign'  => $subtotalUsd,

                // LKR converted values
                'sale_price' => $salePriceUsd * $sale->exchange_rate,
                'subtotal'   => $subtotalLkr,
            ]);

            $totalUsd += $subtotalUsd;
            $totalLkr += $subtotalLkr;
        }

        $sale->update([
            'total_foreign'  => $totalUsd,
            'total'          => $totalLkr,
            'balance_amount' => $totalLkr,
        ]);

        return redirect()->route('export-sales.index');
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
