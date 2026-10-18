<?php

namespace App\Http\Controllers;

use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Ledger;
use App\Models\SubLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::latest()->get();
        return view('expense_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expense_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:expense_categories,name',
            ],
        ]);

        try {

            DB::transaction(function () use ($request) {

                $expenseType = AccountType::where('name', 'Expenses')->firstOrFail();

                $operatingExpenses = AccountGroup::where('account_type_id', $expenseType->id)
                    ->where('name', 'Operating Expenses')->first();


                if (!$operatingExpenses) {

                    throw ValidationException::withMessages([
                        'name' =>
                        'Operating Expenses account group was not found.',
                    ]);
                }

                $liabilityType = AccountType::where('name', 'Liability')->firstOrFail();

                $currentLiability = AccountGroup::where('account_type_id', $liabilityType->id)
                    ->where('name', 'Current Liabilities')->first();

                $accruedExpense = Ledger::where('name', 'Accrued Expenses')
                    ->where('account_group_id', $currentLiability->id)->first();


                if (!$currentLiability) {

                    throw ValidationException::withMessages([
                        'name' =>
                        'Current Liabilities account group was not found.',
                    ]);
                }


                /*
            |--------------------------------------------------------------------------
            | Create Ledger
            |--------------------------------------------------------------------------
            */

                $ledger = Ledger::create([
                    'account_group_id' => $operatingExpenses->id,
                    'name' => $request->name,
                ]);


                $currentLiabilitySubLedger = SubLedger::create([
                    'ledger_id' => $accruedExpense->id,
                    'name' => $request->name,
                ]);


                /*
            |--------------------------------------------------------------------------
            | Create Expense Category
            |--------------------------------------------------------------------------
            */

                ExpenseCategory::create([

                    'name' => $request->name,
                    'type' => 'fixed',
                    'ledger_id' => $ledger->id,
                    'accrued_expense_sub_ledger_id' => $currentLiabilitySubLedger->id,
                    'is_active' => true,
                ]);
            });


            return redirect()
                ->route('expense-categories.index')
                ->with(
                    'success',
                    'Expense category created successfully.'
                );
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create expense category.'
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        //
    }

    public function statement($id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $expenses = Expense::where('expense_category_id', $category->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();

        $statement = collect();
        $balance = 0;

        foreach ($expenses as $expense) {

            // Expense recorded: Debit
            $statement->push([
                'date' => $expense->expense_date,
                'description' => $expense->description ?? 'Expense',
                'debit' => $expense->total_amount,
                'credit' => 0,
            ]);

            // Expense paid: Credit
            if ($expense->is_paid && $expense->paid_date) {
                $statement->push([
                    'date' => $expense->paid_date ?? $expense->expense_date,
                    'description' => 'Payment - ' . ($expense->description ?? 'Expense'),
                    'debit' => 0,
                    'credit' => $expense->total_amount,
                ]);
            }
        }

        // Sort transactions by date and calculate running balance
        $statement = $statement
            ->sortBy('date')
            ->values()
            ->map(function ($row) use (&$balance) {
                $balance += $row['debit'] - $row['credit'];
                $row['balance'] = $balance;

                return $row;
            });

        return view('expense_categories.statement', compact(
            'category',
            'statement'
        ));
    }
}
