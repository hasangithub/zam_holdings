<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Item;
use App\Models\JournalEntryReference;
use App\Models\SubLedger;
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

        $cashBankSubLedgers = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('expenses.create', compact('categories', 'items', 'cashBankSubLedgers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id',],
            'expense_date' => ['required', 'date',],
            'amount' => ['required', 'numeric', 'gt:0',],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'payment_sub_ledger_id' => ['nullable', 'required_if:payment_status,paid', 'exists:sub_ledgers,id'],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
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
                    throw ValidationException::withMessages(['amount' => 'Expense amount must be greater than zero.',]);
                }

                $expense = Expense::create([
                    'branch_id' => $branchId,
                    'expense_category_id' => $category->id,
                    'expense_date' => $request->expense_date,
                    'total_amount' => $amount,
                    'remarks' => $request->remarks,
                ]);

                if ($request->payment_status === 'paid') {
                    $creditLedgerId =  1;
                    $creditSubLedgerId = $request->payment_sub_ledger_id;
                    $debitLedgerId = $category->ledger_id;
                    $debitSubLedgerId = null;
                    $expense->update([
                        'payment_sub_ledger_id' => $request->payment_sub_ledger_id,
                    ]);
                } else {
                    $creditLedgerId = 14;
                    $creditSubLedgerId = $category->accrued_expense_sub_ledger_id;
                    $debitLedgerId = $category->ledger_id;
                    $debitSubLedgerId = null;
                }

                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $expense->expense_date,
                    'description' => 'Operational Expense #' . $expense->id . ' - ' . $category->name,

                    'entries' => [

                        [
                            'ledger_id' =>  $debitLedgerId,
                            'sub_ledger_id' => $debitSubLedgerId,
                            'debit' => $amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => $creditLedgerId,
                            'sub_ledger_id' => $creditSubLedgerId,
                            'debit' => 0,
                            'credit' => $amount,
                        ],

                    ],
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => Expense::class,
                    'model_id' => $expense->id,
                    'action' => 'created',
                ]);
            });


            return redirect()
                ->route('expenses.index')->with('success', 'Expense created successfully.');
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
        $expense = Expense::with([
            'category',
            'paymentSubLedger',
        ])->findOrFail($expense->id);

        $paymentSubLedgers = SubLedger::where('ledger_id', 1)->get();

        return view('expenses.show', compact(
            'expense',
            'paymentSubLedgers'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = ExpenseCategory::all();
        $cashBankSubLedgers = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('expenses.edit', compact('expense', 'categories', 'cashBankSubLedgers'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'payment_sub_ledger_id' => [
                'nullable',
                'required_if:payment_status,paid',
                'exists:sub_ledgers,id',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $branchId = auth()->user()->branch_id;

                $expense = Expense::lockForUpdate()->findOrFail($id);

                $category = ExpenseCategory::lockForUpdate()
                    ->findOrFail($request->expense_category_id);

                if ($category->type !== 'fixed') {
                    throw ValidationException::withMessages([
                        'expense_category_id' =>
                        'Selected category is not a fixed operational expense.',
                    ]);
                }

                $amount = (float) $request->amount;

                if ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Expense amount must be greater than zero.',
                    ]);
                }

                /*
             * Reverse old journal
             */
                Accounting::reverse(Expense::class, $expense->id);

                /*
             * Determine new accounting
             */
                if ($request->payment_status === 'paid') {

                    $creditLedgerId = 1;
                    $creditSubLedgerId = $request->payment_sub_ledger_id;

                    $debitLedgerId = $category->ledger_id;
                    $debitSubLedgerId = null;

                    $paymentSubLedgerId = $request->payment_sub_ledger_id;
                } else {

                    $creditLedgerId = 14;
                    $creditSubLedgerId =
                        $category->accrued_expense_sub_ledger_id;

                    $debitLedgerId = $category->ledger_id;
                    $debitSubLedgerId = null;

                    $paymentSubLedgerId = null;
                }

                /*
             * Update expense
             */
                $expense->update([
                    'expense_category_id' => $category->id,
                    'expense_date' => $request->expense_date,
                    'total_amount' => $amount,
                    'payment_sub_ledger_id' => $paymentSubLedgerId,
                    'remarks' => $request->remarks,
                ]);

                /*
             * Post new journal
             */
                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $expense->expense_date,

                    'description' =>
                    'Operational Expense #' .
                        $expense->id .
                        ' - ' .
                        $category->name,

                    'entries' => [

                        [
                            'ledger_id' => $debitLedgerId,
                            'sub_ledger_id' => $debitSubLedgerId,
                            'debit' => $amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => $creditLedgerId,
                            'sub_ledger_id' => $creditSubLedgerId,
                            'debit' => 0,
                            'credit' => $amount,
                        ],

                    ],
                ]);

                /*
             * Save new journal ID
             */
                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => Expense::class,
                    'model_id' => $expense->id,
                    'action' => 'updated',
                ]);
            });

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense updated successfully.');
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update expense.'
                );
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $expense = Expense::lockForUpdate()->findOrFail($id);

                if ($expense->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'expense' => 'Expense is already cancelled.'
                    ]);
                }

                Accounting::reverse(Expense::class, $expense->id);

                $expense->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense cancelled successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to cancel expense.');
        }
    }

    public function markPaid(Request $request, $id)
    {
        $request->validate([
            'payment_sub_ledger_id' => 'required|exists:sub_ledgers,id',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {

                $expense = Expense::lockForUpdate()->findOrFail($id);

                if ($expense->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'expense' => 'Cancelled expense cannot be marked as paid.',
                    ]);
                }

                if ($expense->is_paid) {
                    throw ValidationException::withMessages([
                        'expense' => 'Expense is already paid.',
                    ]);
                }

                $category = ExpenseCategory::lockForUpdate()
                    ->findOrFail($expense->expense_category_id);

                $journal = Accounting::postJournal([
                    'branch_id' => $expense->branch_id,
                    'date' => now()->toDateString(),
                    'description' => 'Payment of Expense #' . $expense->id,
                    'entries' => [
                        [
                            'ledger_id' => 14,
                            'sub_ledger_id' =>
                            $category->accrued_expense_sub_ledger_id,
                            'debit' => $expense->total_amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 1,
                            'sub_ledger_id' =>
                            $request->payment_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $expense->total_amount,
                        ],
                    ],
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => Expense::class,
                    'model_id' => $expense->id,
                    'action' => 'updated',
                ]);

                $expense->update([
                    'is_paid' => true,
                    'payment_sub_ledger_id' =>
                    $request->payment_sub_ledger_id,
                ]);

                // Create your journal_entry_reference here
            });

            return redirect()
                ->route('expenses.show', $id)
                ->with('success', 'Expense marked as paid successfully.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Unable to mark expense as paid.');
        }
    }
}
