<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Item;
use App\Models\PurchaseInventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackingUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::whereNull('expense_category_id')
            ->latest()
            ->get();

        return view('expenses.packing_usage_index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::where('type', 'packaging')->with('ledger')->get();
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
            'items' => 'required|array|min:1',
            'items.*.item_id' =>
            'required|exists:items,id',
            'items.*.qty' =>
            'required|numeric|gt:0',
        ]);

        try {

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;

                $expense = Expense::create([
                    'branch_id' => $branchId,
                    'expense_category_id' => null,
                    'expense_date' => $request->expense_date,
                    'total_amount' => 0,
                    'remarks' => $request->remarks,
                ]);

                $totalAmount = 0;

                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $qtyRequired = (float) $row['qty'];
                    $remainingToDeduct = $qtyRequired;

                    $batches = PurchaseInventoryItem::where('item_id', $itemId)->where('remaining_qty','>',0)->orderBy('id')->lockForUpdate()->get();

                    $availableQty =
                        $batches->sum(
                            fn($batch) =>
                            (float) $batch->remaining_qty
                        );

                    if ($availableQty < $qtyRequired) {

                        throw ValidationException::withMessages([

                            'items' =>
                            "Not enough packing material stock "
                                . "for item ID {$itemId}. "
                                . "Available: "
                                . number_format(
                                    $availableQty,
                                    3
                                )
                                . ", Required: "
                                . number_format(
                                    $qtyRequired,
                                    3
                                ),

                        ]);
                    }

                    $lineAmount = 0;

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $batchQty = (float) $batch->remaining_qty;
                        $batchCost = (float) $batch->price;

                        $deductQty = min( $batchQty, $remainingToDeduct);

                        $lineAmount += $deductQty * $batchCost;

                        $batch->remaining_qty = $batchQty - $deductQty;
                        $batch->save();

                        $remainingToDeduct -= $deductQty;
                    }

                    if ($remainingToDeduct > 0) {

                        throw new \RuntimeException(
                            'Stock deduction failed.'
                        );
                    }

                    ExpenseDetail::create([
                        'expense_id' => $expense->id,
                        'item_id' => $itemId,
                        'qty' => $qtyRequired,
                        'amount' => $lineAmount,
                    ]);

                    $totalAmount += $lineAmount;
                }

                $expense->update(['total_amount' => $totalAmount]);

                Accounting::postJournal([

                    'branch_id' => $branchId,
                    'date' => $expense->expense_date,
                    'description' => 'Packing Material Usage #' . $expense->id,

                    'entries' => [

                        [
                            'ledger_id' => 11, // COGS
                            'sub_ledger_id' => null, // Packing Material Cost
                            'debit' => $totalAmount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 4, // Inventory
                            'sub_ledger_id' => 2, // Packing Material Inventory
                            'debit' => 0,
                            'credit' => $totalAmount,
                        ],

                    ],

                ]);
            });


            return redirect()
                ->route('packing-usages.index')
                ->with(
                    'success',
                    'Packing Usage created successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create packing usage.'
                );
        }
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
