<?php

namespace App\Http\Controllers;

use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\ExpenseCategory;
use App\Models\Ledger;
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

                $expenseType = AccountType::where(
                    'name',
                    'Expenses'
                )->firstOrFail();

                $operatingExpenses = AccountGroup::where(
                    'account_type_id',
                    $expenseType->id
                )
                    ->where(
                        'name',
                        'Operating Expenses'
                    )
                    ->first();


                if (!$operatingExpenses) {

                    throw ValidationException::withMessages([
                        'name' =>
                        'Operating Expenses account group was not found.',
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


                /*
            |--------------------------------------------------------------------------
            | Create Expense Category
            |--------------------------------------------------------------------------
            */

                ExpenseCategory::create([

                    'name' => $request->name,
                    'type' => 'fixed',
                    'ledger_id' => $ledger->id,
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
}
