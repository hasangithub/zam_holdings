<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Item;
use App\Models\PurchaseInventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::with('category')
            ->whereHas('category', function ($q) {
                $q->where('type', 'packaging');
            })
            ->latest()
            ->get();

        return view('expenses.packing_usage_index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::with('ledger')->get();
        $items = Item::where('item_type', 2)->get();

        return view('expenses.packing_usage_create', compact('categories', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'expense_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {

            $expense = Expense::create([
                'expense_category_id' => 2,
                'expense_date' => $request->expense_date,
                'total_amount' => 0,
                'remarks' => $request->remarks,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $row) {

                $itemId = $row['item_id'];
                $qty    = $row['qty'] ?? 0;
                $cost   = $row['price'] ?? 0;

                $lineAmount = $qty * $cost;
                $totalAmount += $lineAmount;

                if ($qty <= 0) continue;

                ExpenseDetail::create([
                    'expense_id' => $expense->id,
                    'item_id' => $itemId,
                    'qty' => $qty,
                    'amount' => $lineAmount,
                ]);

                // Reduce stock (recommended via stock table)
                $item = Item::find($itemId);
                $purchaseItem = PurchaseInventoryItem::where('item_id', $itemId)
                    ->where('remaining_qty', '>', 0)
                    ->orderBy('id') // FIFO simple
                    ->first();

                if ($purchaseItem) {

                    if ($purchaseItem->remaining_qty >= $qty) {

                        $purchaseItem->remaining_qty -= $qty;
                        $purchaseItem->save();
                    } else {

                        $qtyRemaining = $qty - $purchaseItem->remaining_qty;

                        $purchaseItem->remaining_qty = 0;
                        $purchaseItem->save();

                        // you can continue next batch if needed (FIFO)
                    }
                }

                // JOURNAL
                // Dr Packaging Expense
                // Cr Item Inventory Ledger
            }

            $expense->update([
                'total_amount' => $totalAmount
            ]);
        });

        return redirect()->route('packing-usages.index')
            ->with('success', 'Packing Usages created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load([
            'category',
            'details.item'
        ]);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        //
    }
}
