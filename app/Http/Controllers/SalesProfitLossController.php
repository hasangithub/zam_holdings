<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesProfitLoss;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesProfitLossController extends Controller
{
    /**
     * Display sales invoices and their P&L status.
     */
    public function index(Request $request)
    {
        $query = Sale::with([
            'customer',
            'profitLoss',
        ]);

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('invoice_no', 'like', "%{$search}%")

                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        $sales = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'sales-profit-loss.index',
            compact('sales')
        );
    }


    /**
     * Show invoice selection screen.
     */
    public function create(Request $request)
    {  
        $sale = null;

        $sales = Sale::with('customer')
            ->latest('id')
            ->get();

        if ($request->filled('sale_id')) {

            $sale = Sale::with([
                'customer',
                'items',
                'profitLoss.items',
            ])->findOrFail($request->sale_id);
        }

        return view(
            'sales-profit-loss.form',
            compact('sale', 'sales')
        );
    }


    /**
     * Show P&L for a specific sale.
     *
     * If P&L exists, load existing values.
     * If not, load current sale values as defaults.
     */
    public function edit(Sale $sale)
    {
        $sale->load([
            'customer',
            'items',
            'profitLoss.items',
        ]);

        return view(
            'sales-profit-loss.form',
            compact('sale')
        );
    }


    /**
     * Save P&L.
     *
     * firstOrNew() is intentional:
     *
     * Existing sale P&L → update
     * New sale P&L      → create
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => [
                'required',
                'exists:sales,id',
            ],

            'items' => [
                'required',
                'array',
            ],

            'items.*.product_id' => [
                'nullable',
            ],

            'items.*.item_name' => [
                'nullable',
                'string',
            ],

            'items.*.sales_weight' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.sales_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.other_expense_per_kg' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $sale = Sale::findOrFail(
            $validated['sale_id']
        );

        DB::transaction(function () use (
            $validated,
            $sale
        ) {

            $profitLoss = SalesProfitLoss::firstOrNew([
                'sale_id' => $sale->id,
            ]);

            $profitLoss->save();

            /*
             * We are storing a snapshot.
             *
             * Therefore, when updating the P&L,
             * remove the previous snapshot items
             * and recreate them.
             */
            $profitLoss->items()->delete();

            $this->saveItems(
                $profitLoss,
                $validated['items']
            );
        });

        return redirect()
            ->route('sales-profit-loss.index')
            ->with(
                'success',
                'Sales Profit & Loss saved successfully.'
            );
    }


    /**
     * Update existing P&L.
     */
    public function update(
        Request $request,
        SalesProfitLoss $salesProfitLoss
    ) {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
            ],

            'items.*.product_id' => [
                'nullable',
            ],

            'items.*.item_name' => [
                'nullable',
                'string',
            ],

            'items.*.sales_weight' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.sales_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.other_expense_per_kg' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $salesProfitLoss
        ) {

            /*
             * P&L items are snapshots, so replace
             * the old snapshot with the new one.
             */
            $salesProfitLoss->items()->delete();

            $this->saveItems(
                $salesProfitLoss,
                $validated['items']
            );
        });

        return redirect()
            ->route('sales-profit-loss.index')
            ->with(
                'success',
                'Sales Profit & Loss updated successfully.'
            );
    }


    /**
     * Calculate and save P&L items.
     */
    private function saveItems(
        SalesProfitLoss $profitLoss,
        array $items
    ): void {

        $totalSalesWeight = 0;
        $totalCost = 0;
        $totalSalesAmount = 0;
        $totalProfitLoss = 0;

        foreach ($items as $item) {

            $salesWeight =
                (float) ($item['sales_weight'] ?? 0);

            $salesPrice =
                (float) ($item['sales_price'] ?? 0);

            $purchasePrice =
                (float) ($item['purchase_price'] ?? 0);

            $otherExpense =
                (float) (
                    $item['other_expense_per_kg'] ?? 0
                );

            /*
             * Purchase Cost / Kg
             *
             * Purchase Price + Other Expense
             */
            $purchaseCostPerKg =
                $purchasePrice + $otherExpense;

            /*
             * Total Cost
             */
            $itemTotalCost =
                $salesWeight * $purchaseCostPerKg;

            /*
             * Total Sales
             */
            $salesAmount =
                $salesWeight * $salesPrice;

            /*
             * Profit / Loss
             */
            $profitLossAmount =
                $salesAmount - $itemTotalCost;

            $profitLoss->items()->create([

                'item_id' =>
                    $item['product_id'] ?? null,

                'item_name' =>
                    $item['item_name'] ?? null,

                'sales_weight' =>
                    $salesWeight,

                'sales_price' =>
                    $salesPrice,

                'purchase_price' =>
                    $purchasePrice,

                'other_expense_per_kg' =>
                    $otherExpense,

                'purchase_cost_per_kg' =>
                    $purchaseCostPerKg,

                'total_cost' =>
                    $itemTotalCost,

                'sales_amount' =>
                    $salesAmount,

                'profit_loss' =>
                    $profitLossAmount,
            ]);

            $totalSalesWeight += $salesWeight;
            $totalCost += $itemTotalCost;
            $totalSalesAmount += $salesAmount;
            $totalProfitLoss += $profitLossAmount;
        }

        /*
         * Update P&L header totals.
         */
        $profitLoss->update([

            'total_sales_weight' =>
                $totalSalesWeight,

            'total_cost' =>
                $totalCost,

            'total_sales_amount' =>
                $totalSalesAmount,

            'total_profit_loss' =>
                $totalProfitLoss,
        ]);
    }
}