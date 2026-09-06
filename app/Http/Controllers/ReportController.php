<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InvoiceProfitReport;
use App\Models\InvoiceProfitReportItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function itemProfitAnalysis(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $query = SaleItem::query()
            ->join('items', 'sale_items.item_id', '=', 'items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id');

        if ($from) {
            $query->whereDate('sales.sale_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('sales.sale_date', '<=', $to);
        }

        $report = $query
            ->select(
                'sale_items.item_id',
                'items.name as item_name',

                DB::raw('SUM(sale_items.qty) as sold_qty'),

                DB::raw('SUM(sale_items.qty * sale_items.sale_price) as sales_amount'),

                DB::raw('SUM(sale_items.qty * sale_items.base_price) as cost_amount'),

                DB::raw('SUM((sale_items.qty * sale_items.sale_price) -
                             (sale_items.qty * sale_items.base_price))
                             as gross_profit')
            )
            ->groupBy(
                'sale_items.item_id',
                'items.name'
            )
            ->orderBy('items.name')
            ->get();

        return view(
            'reports.item-profit-analysis',
            compact(
                'report',
                'from',
                'to'
            )
        );
    }

    public function invoiceProfitAnalysis(Request $request)
    {
        // =========================
        // Invoice list (Select2)
        // =========================
        $sales = Sale::with('customer')
            ->latest()
            ->get();

        $sale = null;
        $report = null;
        $items = collect();

        if ($request->filled('sale_id')) {

            // =========================
            // LOAD INVOICE
            // =========================
            $sale = Sale::with('customer')
                ->findOrFail($request->sale_id);

            // =========================
            // LOAD INVOICE AGGREGATED ITEMS
            // =========================
            $invoiceItems = DB::table('sale_items')
                ->join('items', 'sale_items.item_id', '=', 'items.id')
                ->where('sale_items.sale_id', $sale->id)
                ->select(
                    'sale_items.item_id',
                    'items.name',
                    DB::raw('SUM(sale_items.qty) as qty'),
                    DB::raw('ROUND(SUM(sale_items.qty * sale_items.base_price) / SUM(sale_items.qty),2) as avg_cost')
                )
                ->groupBy(
                    'sale_items.item_id',
                    'items.name'
                )
                ->orderBy('items.name')
                ->get();

            // =========================
            // LOAD SAVED REPORT (IF ANY)
            // =========================
            $report = InvoiceProfitReport::with('items')
                ->where('sale_id', $sale->id)
                ->first();

            // =========================
            // OPTIMIZE REPORT LOOKUP
            // =========================
            $reportItems = $report
                ? $report->items->keyBy('item_id')
                : collect();

            // =========================
            // MERGE INVOICE + REPORT
            // =========================
            $items = $invoiceItems->map(function ($invItem) use ($reportItems) {

                $rep = $reportItems[$invItem->item_id] ?? null;

                return (object)[
                    'item_id' => $invItem->item_id,
                    'name' => $invItem->name,

                    // invoice data (source of truth)
                    'qty' => $invItem->qty,
                    'avg_cost' => $invItem->avg_cost,

                    // user editable report data
                    'sale_price_usd' => $rep->sale_price_usd ?? 0,
                    'market_price' => $rep->market_price ?? 0,
                ];
            });
        }

        // =========================
        // RETURN VIEW
        // =========================
        return view(
            'reports.invoice-profit-analysis',
            compact('sales', 'sale', 'items', 'report')
        );
    }

    public function invoiceProfitAnalysisStore(Request $request)
    {
        DB::beginTransaction();

        try {

            // 1. Save header
            $report = InvoiceProfitReport::updateOrCreate(
                ['sale_id' => $request->sale_id],
                [
                    'exchange_rate' => $request->exchange_rate,
                    'fp_perkg' => $request->fp_perkg,
                    'created_by' => auth()->id()
                ]
            );

            // 2. Remove old items
            InvoiceProfitReportItem::where('report_id', $report->id)->delete();

            // 3. Insert new items
            foreach ($request->items as $item) {

                InvoiceProfitReportItem::create([
                    'report_id' => $report->id,
                    'item_id' => $item['item_id'],
                    'sale_price_usd' => $item['sale_price_usd'],
                    'market_price' => $item['market_price']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Report saved successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function indexBranchComparison()
    {
        $branches = Branch::orderBy('name')->get();

        $suppliers = Supplier::orderBy('name')->get();

        $categories = Category::orderBy('name')->get();

        return view(
            'reports.branch-comparison.index',
            compact(
                'branches',
                'suppliers',
                'categories'
            )
        );
    }

    public function generateBranchComparison(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|integer',
            'supplier_id'   => 'required|integer',
            'sales_from'    => 'required|date',
            'sales_to'      => 'required|date',
            'purchase_from' => 'required|date',
            'purchase_to'   => 'required|date',
            'category_id'   => 'nullable|integer',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Sales Qty
    |--------------------------------------------------------------------------
    */

        $salesQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('items', 'items.id', '=', 'sale_items.item_id')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->where('sales.branch_id', $request->branch_id)
            ->whereBetween('sales.sale_date', [
                $request->sales_from,
                $request->sales_to
            ]);

        if ($request->filled('category_id')) {
            $salesQuery->where('items.category_id', $request->category_id);
        }

        $sales = $salesQuery
            ->select(
                'sale_items.item_id',
                DB::raw('SUM(sale_items.qty) as sales_qty')
            )
            ->groupBy('sale_items.item_id')
            ->pluck('sales_qty', 'item_id');



        /*
    |--------------------------------------------------------------------------
    | Purchase Qty
    |--------------------------------------------------------------------------
    */

        $purchaseQuery = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_items.item_id')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->where('purchases.supplier_id', $request->supplier_id)
            ->whereBetween('purchases.purchase_date', [
                $request->purchase_from,
                $request->purchase_to
            ]);

        if ($request->filled('category_id')) {
            $purchaseQuery->where('items.category_id', $request->category_id);
        }

        $purchases = $purchaseQuery
            ->select(
                'purchase_items.item_id',
                DB::raw('SUM(purchase_items.qty) as purchase_qty')
            )
            ->groupBy('purchase_items.item_id')
            ->pluck('purchase_qty', 'item_id');


        /*
    |--------------------------------------------------------------------------
    | Get Items
    |--------------------------------------------------------------------------
    */

        $items = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->when($request->category_id, function ($q) use ($request) {
                $q->where('items.category_id', $request->category_id);
            })
            ->select(
                'items.id',
                'items.name',
                'items.category_id',
                'categories.name as category_name'
            )
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Build Report
    |--------------------------------------------------------------------------
    */

        $report = collect();



        foreach ($items as $item) {

            $purchaseQty = (float) ($purchases[$item->id] ?? 0);

            $salesQty = (float) ($sales[$item->id] ?? 0);

            $difference = $purchaseQty - $salesQty;

            $report->push([

                'category_id' => $item->category_id,
                'category_name' => $item->category_name,
                'item_name' => $item->name,
                'purchase_qty' => $purchaseQty,
                'sales_qty' => $salesQty,
                'difference_qty' => $difference,
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Group by Category
    |--------------------------------------------------------------------------
    */

        $groupedReport = $report->groupBy('category_name');


        return view(
            'reports.branch-comparison.report',
            [
                'report'     => $groupedReport,
                'branch'     => Branch::find($request->branch_id),
                'supplier'   => Supplier::find($request->supplier_id),
                'requestData' => $request->all(),
            ]
        );
    }

    public function customerSummary(Request $request)
    {
        $type = $request->type;

        $customers = Customer::query()
            ->when($type, function ($q) use ($type) {
                $q->where('customer_type', $type);
            })
            ->withSum(['sales as total_sales' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }], 'total')
            ->withMax(['sales as last_sale_date' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }], 'sale_date')
            ->withSum('payments as total_paid', 'amount')
            ->orderBy('name')
            ->get();

        $customers->each(function ($customer) {
            $customer->outstanding =
                ($customer->total_sales ?? 0) -
                ($customer->total_paid ?? 0);
        });

        return view('reports.customer-summary', compact(
            'customers',
            'type'
        ));
    }


    public function supplierSummary(Request $request)
    {
        $type = $request->supplier_type;

        $suppliers = Supplier::query()
            ->where('branch_id', auth()->user()->branch_id)

            ->when($type, function ($query) use ($type) {
                $query->where('supplier_type', $type);
            })

            ->select('suppliers.*')

            // TRADING GOODS PURCHASE TOTAL
            ->selectSub(function ($query) {
                $query->from('purchases')
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.status', '!=', 'cancelled');
            }, 'trading_purchase_total')

            // TRADING GOODS PAYMENT TOTAL
            ->selectSub(function ($query) {
                $query->from('purchase_payments')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('purchase_payments.supplier_id', 'suppliers.id');
            }, 'trading_payment_total')

            // PACKING MATERIAL / OTHERS PURCHASE TOTAL
            ->selectSub(function ($query) {
                $query->from('purchase_inventories')
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('purchase_inventories.supplier_id', 'suppliers.id')
                    ->where('purchase_inventories.status', '!=', 'cancelled');
            }, 'inventory_purchase_total')

            // PACKING MATERIAL / OTHERS PAYMENT TOTAL
            ->selectSub(function ($query) {
                $query->from('purchase_inventory_payments')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn(
                        'purchase_inventory_payments.supplier_id',
                        'suppliers.id'
                    );
            }, 'inventory_payment_total')

            // LAST TRADING PURCHASE
            ->selectSub(function ($query) {
                $query->from('purchases')
                    ->selectRaw('MAX(purchase_date)')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.status', '!=', 'cancelled');
            }, 'trading_last_purchase')

            // LAST INVENTORY PURCHASE
            ->selectSub(function ($query) {
                $query->from('purchase_inventories')
                    ->selectRaw('MAX(purchase_date)')
                    ->whereColumn(
                        'purchase_inventories.supplier_id',
                        'suppliers.id'
                    )
                    ->where('purchase_inventories.status', '!=', 'cancelled');
            }, 'inventory_last_purchase')

            ->orderBy('name')
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Calculate supplier balance based on supplier type
    |--------------------------------------------------------------------------
    */

        $suppliers->each(function ($supplier) {

            if ($supplier->supplier_type === 'Trading Goods') {

                // Trading Goods
                $supplier->purchase_total =
                    (float) $supplier->trading_purchase_total;

                $supplier->payment_total =
                    (float) $supplier->trading_payment_total;

                $supplier->last_purchase =
                    $supplier->trading_last_purchase;
            } else {

                // Packing Material + Others
                $supplier->purchase_total =
                    (float) $supplier->inventory_purchase_total;

                $supplier->payment_total =
                    (float) $supplier->inventory_payment_total;

                $supplier->last_purchase =
                    $supplier->inventory_last_purchase;
            }

            $supplier->outstanding =
                $supplier->purchase_total -
                $supplier->payment_total;
        });


        return view(
            'reports.supplier-summary',
            compact('suppliers', 'type')
        );
    }
}
