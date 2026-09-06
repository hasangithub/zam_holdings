<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\ExpenseDetailFifo;
use App\Models\Item;
use App\Models\JournalEntry;
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

                    $batches = PurchaseInventoryItem::where('item_id', $itemId)->where('remaining_qty', '>', 0)->orderBy('id')->lockForUpdate()->get();

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

                    $expenseDetail = ExpenseDetail::create([
                        'expense_id' => $expense->id,
                        'item_id' => $itemId,
                        'qty' => $qtyRequired,
                        'amount' => 0,
                    ]);

                    $lineAmount = 0;

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $batchQty = (float) $batch->remaining_qty;
                        $batchCost = (float) $batch->price;

                        $deductQty = min($batchQty, $remainingToDeduct);

                        $lineAmount += $deductQty * $batchCost;

                        $batch->remaining_qty = $batchQty - $deductQty;
                        $batch->save();

                        ExpenseDetailFifo::create([
                            'expense_detail_id' =>
                            $expenseDetail->id,

                            'purchase_inventory_item_id' =>
                            $batch->id,

                            'qty' => $deductQty,

                            'unit_cost' => $batchCost,
                        ]);

                        $remainingToDeduct -= $deductQty;
                    }

                    if ($remainingToDeduct > 0) {

                        throw new \RuntimeException(
                            'Stock deduction failed.'
                        );
                    }

                    $expenseDetail->update([
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
    public function edit(int $id)
    {
        $expense = Expense::with([
            'details.item',
        ])->findOrFail($id);

        $items = Item::where('item_type', 2) // PACKAGING_ITEM
            ->orderBy('name')
            ->get();

        return view('expenses.packing_usage_edit', compact(
            'expense',
            'items'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|gt:0',
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $branchId = auth()->user()->branch_id;

                /*
            |--------------------------------------------------------------------------
            | Lock Expense
            |--------------------------------------------------------------------------
            */

                $expense = Expense::lockForUpdate()
                    ->with('details.fifos')
                    ->findOrFail($id);

                /*
            |--------------------------------------------------------------------------
            | Restore Old FIFO Stock
            |--------------------------------------------------------------------------
            */

                foreach ($expense->details as $detail) {

                    foreach ($detail->fifos as $fifo) {

                        $inventoryItem =
                            PurchaseInventoryItem::lockForUpdate()
                            ->find($fifo->purchase_inventory_item_id);

                        if ($inventoryItem) {

                            $inventoryItem->remaining_qty =
                                (float) $inventoryItem->remaining_qty
                                + (float) $fifo->qty;

                            $inventoryItem->save();
                        }
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | Delete Old FIFO Records
            |--------------------------------------------------------------------------
            */

                foreach ($expense->details as $detail) {

                    $detail->fifos()->delete();
                }

                /*
            |--------------------------------------------------------------------------
            | Delete Old Expense Details
            |--------------------------------------------------------------------------
            */

                $expense->details()->delete();

                /*
            |--------------------------------------------------------------------------
            | Update Expense Header
            |--------------------------------------------------------------------------
            */

                $expense->update([
                    'expense_date' => $request->expense_date,
                    'remarks' => $request->remarks,
                    'total_amount' => 0,
                ]);

                $totalAmount = 0;

                /*
            |--------------------------------------------------------------------------
            | Create New Details + FIFO
            |--------------------------------------------------------------------------
            */

                foreach ($request->items as $row) {

                    $itemId = (int) $row['item_id'];
                    $qtyRequired = (float) $row['qty'];
                    $remainingToDeduct = $qtyRequired;

                    /*
                |--------------------------------------------------------------------------
                | Get FIFO Stock
                |--------------------------------------------------------------------------
                */

                    $batches = PurchaseInventoryItem::where(
                        'item_id',
                        $itemId
                    )
                        ->where('remaining_qty', '>', 0)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $availableQty = $batches->sum(
                        fn($batch) =>
                        (float) $batch->remaining_qty
                    );

                    if ($availableQty < $qtyRequired) {

                        $item = Item::find($itemId);

                        throw ValidationException::withMessages([
                            'items' =>
                            "Not enough packing material stock "
                                . "for "
                                . ($item?->name ?? "Item #{$itemId}")
                                . ". Available: "
                                . number_format($availableQty, 3)
                                . ", Required: "
                                . number_format($qtyRequired, 3),
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Create Expense Detail
                |--------------------------------------------------------------------------
                */

                    $expenseDetail = ExpenseDetail::create([
                        'expense_id' => $expense->id,
                        'item_id' => $itemId,
                        'qty' => $qtyRequired,
                        'amount' => 0,
                    ]);

                    $lineAmount = 0;

                    /*
                |--------------------------------------------------------------------------
                | FIFO Deduction
                |--------------------------------------------------------------------------
                */

                    foreach ($batches as $batch) {

                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $batchQty =
                            (float) $batch->remaining_qty;

                        $batchCost =
                            (float) $batch->price;

                        $deductQty = min(
                            $batchQty,
                            $remainingToDeduct
                        );

                        $fifoAmount =
                            $deductQty * $batchCost;

                        $lineAmount += $fifoAmount;

                        /*
                    |--------------------------------------------------------------------------
                    | Reduce Inventory
                    |--------------------------------------------------------------------------
                    */

                        $batch->remaining_qty =
                            $batchQty - $deductQty;

                        $batch->save();

                        /*
                    |--------------------------------------------------------------------------
                    | Create New FIFO
                    |--------------------------------------------------------------------------
                    */

                        ExpenseDetailFifo::create([
                            'expense_detail_id' =>
                            $expenseDetail->id,

                            'purchase_inventory_item_id' =>
                            $batch->id,

                            'qty' => $deductQty,

                            'unit_cost' => $batchCost,
                        ]);

                        $remainingToDeduct -= $deductQty;
                    }

                    if ($remainingToDeduct > 0) {

                        throw new \RuntimeException(
                            'Stock deduction failed.'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Update Detail Amount
                |--------------------------------------------------------------------------
                */

                    $expenseDetail->update([
                        'amount' => $lineAmount,
                    ]);

                    $totalAmount += $lineAmount;
                }

                /*
            |--------------------------------------------------------------------------
            | Update Expense Total
            |--------------------------------------------------------------------------
            */

                $expense->update([
                    'total_amount' => $totalAmount,
                ]);

                /*
            |--------------------------------------------------------------------------
            | Accounting Journal
            |--------------------------------------------------------------------------
            |
            | Delete the old journal and post the new one.
            | This keeps the journal equal to the edited expense.
            |
            */

                $journal = JournalEntry::where(
                    'description',
                    'Packing Material Usage #' . $expense->id
                )
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                if ($journal) {

                    $journal->details()->delete();
                    $journal->delete();
                }

                Accounting::postJournal([

                    'branch_id' => $branchId,

                    'date' => $expense->expense_date,

                    'description' =>
                    'Packing Material Usage #' . $expense->id,

                    'entries' => [

                        [
                            'ledger_id' => 11,
                            'sub_ledger_id' => null,
                            'debit' => $totalAmount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => 2,
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
                    'Packing Usage updated successfully.'
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
                    'Unable to update packing usage.'
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {

            DB::transaction(function () use ($id) {

                $branchId = auth()->user()->branch_id;

                /*
            |--------------------------------------------------------------------------
            | Lock Expense
            |--------------------------------------------------------------------------
            */

                $expense = Expense::lockForUpdate()
                    ->with([
                        'details.fifos',
                    ])
                    ->findOrFail($id);

                if ($expense->status === 'cancelled') {

                    throw ValidationException::withMessages([
                        'expense' =>
                        'This packing usage is already cancelled.'
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Restore FIFO Stock
            |--------------------------------------------------------------------------
            */

                foreach ($expense->details as $detail) {

                    foreach ($detail->fifos as $fifo) {

                        $inventoryItem =
                            PurchaseInventoryItem::lockForUpdate()
                            ->find(
                                $fifo->purchase_inventory_item_id
                            );

                        if (!$inventoryItem) {

                            throw ValidationException::withMessages([
                                'expense' =>
                                'Purchase inventory item not found.'
                            ]);
                        }

                        $inventoryItem->remaining_qty =
                            (float) $inventoryItem->remaining_qty
                            + (float) $fifo->qty;

                        $inventoryItem->save();
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | Reverse Journal
            |--------------------------------------------------------------------------
            */

                Accounting::postJournal([

                    'branch_id' => $branchId,

                    'date' => now()->toDateString(),

                    'description' =>
                    'Packing Material Usage Cancellation #'
                        . $expense->id,

                    'entries' => [

                        // Reverse COGS
                        [
                            'ledger_id' => 11,
                            'sub_ledger_id' => null,
                            'debit' => 0,
                            'credit' => $expense->total_amount,
                        ],

                        // Restore Inventory
                        [
                            'ledger_id' => 4,
                            'sub_ledger_id' => 2,
                            'debit' => $expense->total_amount,
                            'credit' => 0,
                        ],

                    ],

                ]);

                /*
            |--------------------------------------------------------------------------
            | Cancel Expense
            |--------------------------------------------------------------------------
            */

                $expense->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()
                ->route('expenses.packing_usage_index')
                ->with(
                    'success',
                    'Packing Usage cancelled successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {

            return back()
                ->with(
                    'error',
                    'Unable to cancel packing usage.'
                );
        }
    }
}
