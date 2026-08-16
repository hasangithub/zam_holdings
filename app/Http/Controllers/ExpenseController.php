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
        $categories = ExpenseCategory::where('type', 'fixed')->with('ledger')->get();
        $items = Item::where('item_type', 2)->get();

        return view('expenses.create', compact('categories', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => ['required','exists:expense_categories,id',],
            'expense_date' => ['required','date',],
            'amount' => ['required','numeric','gt:0',],
            'remarks' => ['nullable','string','max:1000',
            ],
        ]);

        try {

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;

                $category = ExpenseCategory::lockForUpdate()->findOrFail($request->expense_category_id);

                if ($category->type !== 'fixed') {

                    throw ValidationException::withMessages([

                        'expense_category_id' =>
                        'Selected category is not a fixed operational expense.',

                    ]);
                }

                $amount = (float) $request->amount;

                if ($amount <= 0) {
                    throw ValidationException::withMessages(['amount' => 'Expense amount must be greater than zero.', ]);
                }

                $expense = Expense::create([
                    'branch_id' => $branchId,
                    'expense_category_id' => $category->id,
                    'expense_date' => $request->expense_date,
                    'total_amount' => $amount,
                    'remarks' => $request->remarks,
                ]);

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $expense->expense_date,
                    'description' => 'Operational Expense #' . $expense->id . ' - ' . $category->name,

                    'entries' => [

                        [
                            'ledger_id' => $category->ledger_id,
                            'sub_ledger_id' => null,
                            'debit' => $amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 1,
                            'sub_ledger_id' => null,
                            'debit' => 0,
                            'credit' => $amount,
                        ],

                    ],

                ]);
            });


            return redirect()
                ->route('expenses.index') ->with('success','Expense created successfully.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create expense.'
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
