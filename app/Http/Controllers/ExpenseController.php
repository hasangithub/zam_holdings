<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Item;
use App\Models\PurchaseInventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::with('category')
            ->whereHas('category', function ($q) {
                $q->where('type', 'fixed');
            })
            ->latest()
            ->get();

        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::with('ledger')->get();
        $items = Item::where('item_type', 2)->get();

        return view('expenses.create', compact('categories', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required',
            'expense_date' => 'required|date',
        ]);



        DB::transaction(function () use ($request) {

            $category = ExpenseCategory::findOrFail($request->expense_category_id);

            $expense = Expense::create([
                'expense_category_id' => $category->id,
                'expense_date' => $request->expense_date,
                'total_amount' => $request->amount ?? 0,
                'remarks' => $request->remarks,
            ]);

            // FIXED EXPENSE
            if ($category->type == 'fixed') {

                ExpenseDetail::create([
                    'expense_id' => $expense->id,
                    'amount' => $request->amount,
                    'description' => $request->description,
                ]);

                // JOURNAL (example)
                // Dr Expense Ledger
                // Cr Cash/Bank
            }

            // PACKAGING EXPENSE
            if ($category->type == 'packaging') {

                foreach ($request->item_id as $key => $itemId) {

                    $qty = $request->qty[$key] ?? 0;
                    $cost = $request->cost[$key] ?? 0;

                    if ($qty <= 0) continue;

                    ExpenseDetail::create([
                        'expense_id' => $expense->id,
                        'item_id' => $itemId,
                        'qty' => $qty,
                        'amount' => $qty * $cost,
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
            }
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense created successfully');
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
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = ExpenseCategory::all();

        return view('expenses.edit', compact('expense', 'categories'));
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
