<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InvoiceProfitReport;
use App\Models\InvoiceProfitReportItem;
use App\Models\JournalEntryDetail;
use App\Models\Ledger;
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

    public function customerSummaryLocal(Request $request)
    {
        $customers = Customer::query()
            ->where('branch_id', auth()->user()->branch_id)
            ->where('customer_type', 'local')
            ->selectSub(function ($query) {
                $query->from('journal_entry_details')
                    ->selectRaw('COALESCE(SUM(debit), 0)')
                    ->whereColumn(
                        'journal_entry_details.journal_entry_id',
                        'customers.opening_journal_entry_id'
                    );
            }, 'opening_balance')
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

             $customer->opening_balance =
                (float) ($customer->opening_balance ?? 0);

            $customer->total_sales =
                (float) ($customer->total_sales ?? 0)
                + $customer->opening_balance; 
                
            $customer->outstanding =
                ($customer->total_sales ?? 0) -
                ($customer->total_paid ?? 0);
        });

        return view('reports.customer-summary-local', compact(
            'customers'
        ));
    }

    public function customerSummaryExport(Request $request)
    {
        $customers = Customer::query()
            ->where('branch_id', auth()->user()->branch_id)
            ->where('customer_type', 'export')
            ->selectSub(function ($query) {
                $query->from('journal_entry_details')
                    ->selectRaw('COALESCE(SUM(debit), 0)')
                    ->whereColumn(
                        'journal_entry_details.journal_entry_id',
                        'customers.opening_journal_entry_id'
                    );
            }, 'opening_balance')
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

            $customer->opening_balance =
                (float) ($customer->opening_balance ?? 0);

            $customer->total_sales =
                (float) ($customer->total_sales ?? 0)
                + $customer->opening_balance;

            $customer->outstanding =
                ($customer->total_sales ?? 0) -
                ($customer->total_paid ?? 0);
        });

        return view('reports.customer-summary-export', compact(
            'customers'
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
            // SUPPLIER OPENING BALANCE FROM JOURNAL
            ->selectSub(function ($query) {
                $query->from('journal_entry_details')
                    ->selectRaw('COALESCE(SUM(debit), 0)')
                    ->whereColumn(
                        'journal_entry_details.journal_entry_id',
                        'suppliers.opening_journal_entry_id'
                    );
            }, 'opening_balance')

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

            $supplier->opening_balance = (float) ($supplier->opening_balance ?? 0);

            $supplier->purchase_total += $supplier->opening_balance;

            $supplier->outstanding =
                $supplier->purchase_total -
                $supplier->payment_total;
        });


        return view(
            'reports.supplier-summary',
            compact('suppliers', 'type')
        );
    }

    /**
     * Trial Balance
     */


    public function trialBalance(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->from_date
            ?? now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->to_date
            ?? now()->format('Y-m-d');

        // Get all ledgers, including ledgers without transactions
        $ledgers = Ledger::with('subLedgers')
            ->orderBy('id')
            ->get();

        // Get journal totals for the selected period
        $journalTotals = JournalEntryDetail::query()
            ->select(
                'ledger_id',
                'sub_ledger_id',
                DB::raw('COALESCE(SUM(debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(credit), 0) as total_credit')
            )
            ->whereHas('journalEntry', function ($query) use (
                $fromDate,
                $toDate
            ) {
                $query->whereDate('journal_date', '>=', $fromDate)
                    ->whereDate('journal_date', '<=', $toDate);
            })
            ->groupBy('ledger_id', 'sub_ledger_id')
            ->get();

        // Build lookup and combine NULL/0 subledger IDs as ledger-own entries
        $journalLookup = [];

        foreach ($journalTotals as $row) {
            $subLedgerId = $row->sub_ledger_id ?? 0;

            $key = $row->ledger_id . '-' . $subLedgerId;

            if (!isset($journalLookup[$key])) {
                $journalLookup[$key] = [
                    'debit'  => 0,
                    'credit' => 0,
                ];
            }

            $journalLookup[$key]['debit'] += (float) $row->total_debit;
            $journalLookup[$key]['credit'] += (float) $row->total_credit;
        }

        $ledgerGroups = collect();

        foreach ($ledgers as $ledger) {

            // Ledger's own journal entries
            $ledgerKey = $ledger->id . '-0';

            $ledgerOwnDebit = $journalLookup[$ledgerKey]['debit'] ?? 0;
            $ledgerOwnCredit = $journalLookup[$ledgerKey]['credit'] ?? 0;

            $subLedgers = collect();

            $subLedgerDebitTotal = 0;
            $subLedgerCreditTotal = 0;

            foreach ($ledger->subLedgers as $subLedger) {

                $subLedgerKey = $ledger->id . '-' . $subLedger->id;

                $debit = $journalLookup[$subLedgerKey]['debit'] ?? 0;
                $credit = $journalLookup[$subLedgerKey]['credit'] ?? 0;

                $subLedgerDebitTotal += $debit;
                $subLedgerCreditTotal += $credit;

                // Net subledger balance
                $balance = $debit - $credit;

                $subLedgers->push([
                    'id' => $subLedger->id,
                    'code' => $subLedger->code ?? $subLedger->id,
                    'name' => $subLedger->name
                        ?? $subLedger->title
                        ?? 'Sub Ledger',

                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ]);
            }

            // Ledger total = own entries + all subledger entries
            $ledgerDebit = $ledgerOwnDebit + $subLedgerDebitTotal;
            $ledgerCredit = $ledgerOwnCredit + $subLedgerCreditTotal;

            // Net ledger balance
            $ledgerBalance = $ledgerDebit - $ledgerCredit;

            $ledgerDebitBalance = $ledgerBalance > 0
                ? $ledgerBalance
                : 0;

            $ledgerCreditBalance = $ledgerBalance < 0
                ? abs($ledgerBalance)
                : 0;

            $ledgerGroups->push([
                'id' => $ledger->id,
                'code' => $ledger->code
                    ?? $ledger->ledger_code
                    ?? $ledger->id,

                'name' => $ledger->name
                    ?? $ledger->title
                    ?? 'Ledger',

                'debit' => $ledgerDebitBalance,
                'credit' => $ledgerCreditBalance,

                'sub_ledgers' => $subLedgers->values()->all(),
            ]);
        }

        // Grand totals: sum ledger balances only, not subledger rows again
        $totals = [
            'debit' => $ledgerGroups->sum('debit'),
            'credit' => $ledgerGroups->sum('credit'),
        ];

        return view('reports.trial_balance', compact(
            'ledgerGroups',
            'totals',
            'fromDate',
            'toDate'
        ));
    }


    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input(
            'as_of_date',
            now()->toDateString()
        );

        $branchId = (int) auth()->user()->branch_id;

        // Only Branch 1 can select the report scope.
        $request->validate([
            'as_of_date' => ['nullable', 'date'],
            'branch_scope' => ['nullable', 'in:own,all'],
        ]);

        if ($branchId === 1) {
            $branchScope = $request->input('branch_scope', 'all');

            if ($branchScope === 'own') {
                $branchIds = collect([1]);
            } else {
                $branchIds = Branch::pluck('id');
                $branchScope = 'all';
            }
        } else {
            // All other branches can only see their own data.
            $branchIds = collect([$branchId]);
            $branchScope = 'own';
        }

        $accountTypes = AccountType::with(
            'accountGroups.ledgers'
        )->get();

        $assets = [];
        $liabilities = [];
        $equity = [];

        $assetsTotal = 0;
        $liabilitiesTotal = 0;
        $equityTotal = 0;
        $incomeTotal = 0;
        $expensesTotal = 0;

        foreach ($accountTypes as $type) {
            $typeName = strtolower(trim($type->name));

            foreach ($type->accountGroups as $group) {
                $groupName = strtolower(trim($group->name));

                foreach ($group->ledgers as $ledger) {

                    $details = JournalEntryDetail::where(
                        'ledger_id',
                        $ledger->id
                    )->whereHas('journalEntry', function ($query) use (
                        $asOfDate,
                        $branchIds
                    ) {
                        $query->whereDate(
                            'journal_date',
                            '<=',
                            $asOfDate
                        )->whereIn('branch_id', $branchIds);
                    });

                    $debit = (clone $details)->sum('debit');
                    $credit = (clone $details)->sum('credit');

                    $row = [
                        'group' => $group->name,
                        'ledger' => $ledger->name,
                        'balance' => 0,
                    ];

                    if (in_array($typeName, ['assets', 'asset'])) {
                        $row['balance'] = $debit - $credit;

                        $assets[] = $row;
                        $assetsTotal += $row['balance'];
                    } elseif (in_array(
                        $typeName,
                        ['liabilities', 'liability']
                    )) {
                        if ($groupName === 'equity') {
                            $row['balance'] = $credit - $debit;

                            $equity[] = $row;
                            $equityTotal += $row['balance'];
                        } else {
                            $row['balance'] = $credit - $debit;

                            $liabilities[] = $row;
                            $liabilitiesTotal += $row['balance'];
                        }
                    } elseif (in_array(
                        $typeName,
                        ['income', 'incomes']
                    )) {
                        $incomeTotal += $credit - $debit;
                    } elseif (in_array(
                        $typeName,
                        ['expenses', 'expense']
                    )) {
                        $expensesTotal += $debit - $credit;
                    }
                }
            }
        }

        // Accumulated profit/loss up to the selected date.
        $profitLoss = $incomeTotal - $expensesTotal;

        $equity[] = [
            'group' => 'Current Profit / Loss',
            'ledger' => 'Accumulated Profit / Loss',
            'balance' => $profitLoss,
        ];

        $equityTotal += $profitLoss;

        return view('reports.balance-sheet', compact(
            'asOfDate',
            'branchScope',
            'assets',
            'liabilities',
            'equity',
            'assetsTotal',
            'liabilitiesTotal',
            'equityTotal'
        ));
    }
}
