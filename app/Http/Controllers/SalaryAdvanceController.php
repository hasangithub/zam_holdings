<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdvancePayment;
use App\Models\Employee;
use App\Models\Ledger;
use App\Models\SubLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\JournalEntryReference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryAdvanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $salaryAdvances = SalaryAdvance::with([
            'employee',
            'payments'
        ])
            ->where('branch_id', auth()->user()->branch_id)
            ->latest('advance_date')
            ->latest('id')
            ->get();

        return view(
            'salary-advances.index',
            compact('salaryAdvances')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $employees = Employee::orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Salary Advance Ledger
        |--------------------------------------------------------------------------
        */

        $salaryAdvanceLedger = Ledger::where(
            'id',
            '2'
        )->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Cash / Bank Ledger
        |--------------------------------------------------------------------------
        */

        $cashBankLedger = Ledger::where(
            'id',
            '1'
        )->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Current User Cash / Bank Accounts
        |--------------------------------------------------------------------------
        */

        $paymentAccounts = SubLedger::where(
            'ledger_id',
            $cashBankLedger->id
        )
            ->where(
                'branch_id',
                auth()->user()->branch_id
            )

            ->orderBy('name')
            ->get();

        return view(
            'salary-advances.create',
            compact(
                'employees',
                'salaryAdvanceLedger',
                'paymentAccounts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE ADVANCE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'advance_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_sub_ledger_id' => ['required', 'exists:sub_ledgers,id'],
            'description' => ['nullable', 'string'],
        ]);

        $employee = Employee::where('id', $request->employee_id)->firstOrFail();

        $salaryAdvanceLedger = Ledger::where('id', '2')->firstOrFail();
        $cashBankLedger = Ledger::where('id', '1')->firstOrFail();

        $paymentAccount = SubLedger::where('id', $request->payment_sub_ledger_id)
            ->where('ledger_id', $cashBankLedger->id)
            ->where('branch_id', auth()->user()->branch_id)
            ->first();

        if (!$paymentAccount) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment_sub_ledger_id' =>
                    'Invalid payment account.'
                ]);
        }

        DB::transaction(function () use ($request, $paymentAccount) {

            $journal = JournalEntry::create([
                'branch_id' => auth()->user()->branch_id,
                'journal_date' => $request->advance_date,
                'description' => 'Salary Advance - Employee',
                'created_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Dr Salary Advance
            |--------------------------------------------------------------------------
            */

            JournalEntryDetail::create([
                'journal_entry_id' => $journal->id,
                'ledger_id' => 2,
                'sub_ledger_id' => null,
                'debit' => $request->amount,
                'credit' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cr Cash / Bank
            |--------------------------------------------------------------------------
            */

            JournalEntryDetail::create([
                'journal_entry_id' => $journal->id,
                'ledger_id' => $paymentAccount->ledger_id,
                'sub_ledger_id' => $paymentAccount->id,
                'debit' => 0,
                'credit' => $request->amount,
            ]);

            SalaryAdvance::create([
                'branch_id' => auth()->user()->branch_id,
                'employee_id' => $request->employee_id,
                'advance_date' => $request->advance_date,
                'amount' => $request->amount,
                'salary_advance_sub_ledger_id' => null,
                'payment_sub_ledger_id' => $paymentAccount->id,
                'journal_entry_id' => $journal->id,
                'status' => 'pending',
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('salary-advances.index')
            ->with(
                'success',
                'Salary advance created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(SalaryAdvance $salaryAdvance)
    {
        if (
            $salaryAdvance->branch_id
            != auth()->user()->branch_id
        ) {
            abort(403);
        }

        $salaryAdvance->load([
            'employee',
            'payments.paymentSubLedger',
            'payments.creator',
            'salaryAdvanceSubLedger',
            'paymentSubLedger',
            'journalEntry',
        ]);

        return view(
            'salary-advances.show',
            compact('salaryAdvance')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RETURN FORM
    |--------------------------------------------------------------------------
    */

    public function returnForm(SalaryAdvance $salaryAdvance)
    {
        if (
            $salaryAdvance->branch_id
            != auth()->user()->branch_id
        ) {
            abort(403);
        }

        $returnedAmount =
            $salaryAdvance->payments()->sum('amount');

        $outstanding =
            $salaryAdvance->amount - $returnedAmount;

        if ($outstanding <= 0) {
            return redirect()
                ->route(
                    'salary-advances.show',
                    $salaryAdvance
                )
                ->with(
                    'error',
                    'This salary advance is already fully returned.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Cash / Bank Accounts
        |--------------------------------------------------------------------------
        */

        $cashBankLedger = Ledger::where('id', 1)->firstOrFail();

        $paymentAccounts = SubLedger::where('ledger_id', $cashBankLedger->id)->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')->get();

        return view(
            'salary-advances.return',
            compact(
                'salaryAdvance',
                'outstanding',
                'paymentAccounts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE RETURN
    |--------------------------------------------------------------------------
    */

    public function storeReturn(
        Request $request,
        SalaryAdvance $salaryAdvance
    ) {
        if (
            $salaryAdvance->branch_id
            != auth()->user()->branch_id
        ) {
            abort(403);
        }

        $returnedAmount = $salaryAdvance->payments()->sum('amount');
        $outstanding = $salaryAdvance->amount - $returnedAmount;

        if ($outstanding <= 0) {
            return back()->withErrors([
                'amount' =>
                'This salary advance is already fully returned.'
            ]);
        }

        $request->validate([
            'payment_date' => [
                'required',
                'date'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $outstanding
            ],

            'payment_sub_ledger_id' => [
                'required',
                'exists:sub_ledgers,id'
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255'
            ],

            'note' => [
                'nullable',
                'string'
            ],
        ]);

        $cashBankLedger = Ledger::where('id', 1)->firstOrFail();

        $paymentAccount = SubLedger::where('id', $request->payment_sub_ledger_id)
            ->where('ledger_id', $cashBankLedger->id)->where('branch_id', auth()->user()->branch_id)->first();

        if (!$paymentAccount) {
            return back()->withErrors([
                'payment_sub_ledger_id' =>
                'Invalid payment account.'
            ]);
        }

        DB::transaction(function () use (
            $request,
            $salaryAdvance,
            $paymentAccount,
            $outstanding
        ) {

            /*
            |--------------------------------------------------------------------------
            | Journal
            |--------------------------------------------------------------------------
            */

            $journal = JournalEntry::create([
                'branch_id' =>
                auth()->user()->branch_id,

                'journal_date' =>
                $request->payment_date,

                'description' =>
                'Salary Advance Return - '
                    . $salaryAdvance->employee->name,

                'created_by' =>
                auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Dr Cash / Bank
            |--------------------------------------------------------------------------
            */

            JournalEntryDetail::create([
                'journal_entry_id' => $journal->id,
                'ledger_id' => $paymentAccount->ledger_id,
                'sub_ledger_id' => $paymentAccount->id,
                'debit' => $request->amount,
                'credit' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cr Salary Advance
            |--------------------------------------------------------------------------
            */

            JournalEntryDetail::create([
                'journal_entry_id' => $journal->id,
                'ledger_id' => 2,
                'sub_ledger_id' => null,
                'debit' => 0,
                'credit' => $request->amount,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $salaryAdvancePayment = SalaryAdvancePayment::create([
                'salary_advance_id' =>
                $salaryAdvance->id,

                'payment_date' =>
                $request->payment_date,

                'amount' =>
                $request->amount,

                'payment_sub_ledger_id' =>
                $paymentAccount->id,

                'journal_entry_id' =>
                $journal->id,

                'reference' =>
                $request->reference,

                'note' =>
                $request->note,

                'created_by' =>
                auth()->id(),
            ]);

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => SalaryAdvancePayment::class,
                'model_id' => $salaryAdvancePayment->id,
                'action' => 'created',
            ]);

            /*
            |--------------------------------------------------------------------------
            | New Balance
            |--------------------------------------------------------------------------
            */

            $newOutstanding =
                $outstanding - $request->amount;

            if ($newOutstanding <= 0) {

                $salaryAdvance->status =
                    'fully_returned';
            } else {

                $salaryAdvance->status =
                    'partial';
            }

            $salaryAdvance->save();
        });

        return redirect()
            ->route(
                'salary-advances.show',
                $salaryAdvance
            )
            ->with(
                'success',
                'Salary advance return recorded successfully.'
            );
    }

    public function updateReturn(
        Request $request,
        int $employeeId,
        int $salaryAdvanceId,
        int $paymentId
    ) {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
        ]);

        try {

            DB::transaction(function () use (
                $request,
                $employeeId,
                $salaryAdvanceId,
                $paymentId
            ) {

                $branchId = auth()->user()->branch_id;

                // Lock employee.
                $employee = Employee::lockForUpdate()
                    ->findOrFail($employeeId);

                // Lock salary advance.
                $advance = SalaryAdvance::where('branch_id', $branchId)
                    ->where('employee_id', $branchId)
                    ->lockForUpdate()
                    ->findOrFail($salaryAdvanceId);

                // Lock existing repayment.
                $payment = SalaryAdvancePayment::where(
                    'salary_advance_id',
                    $advance->id
                )
                    ->lockForUpdate()
                    ->findOrFail($paymentId);

                $amount = (float) $request->amount;

                // Calculate outstanding excluding the current payment.
                $otherPayments = SalaryAdvancePayment::where(
                    'salary_advance_id',
                    $advance->id
                )
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                $outstanding = (float) $advance->amount - (float) $otherPayments;

                if ($amount > $outstanding) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount cannot exceed the outstanding salary advance balance.',
                    ]);
                }

                // Find the latest journal reference.
                $reference = JournalEntryReference::where(
                    'model_type',
                    SalaryAdvancePayment::class
                )
                    ->where('model_id', $payment->id)
                    ->whereIn('action', ['created', 'updated'])
                    ->latest('id')
                    ->first();

                if (!$reference) {
                    throw ValidationException::withMessages([
                        'amount' => 'The original payment journal could not be found.',
                    ]);
                }

                // Get original journal debit and credit entries.
                $debitEntry = DB::table('journal_entry_details')
                    ->where('journal_entry_id', $reference->journal_entry_id)
                    ->where('debit', '>', 0)
                    ->first();

                $creditEntry = DB::table('journal_entry_details')
                    ->where('journal_entry_id', $reference->journal_entry_id)
                    ->where('credit', '>', 0)
                    ->first();

                if (!$debitEntry || !$creditEntry) {
                    throw ValidationException::withMessages([
                        'amount' => 'Original payment journal entries could not be identified.',
                    ]);
                }

                // Reverse previous payment journal.
                Accounting::reverse(
                    SalaryAdvancePayment::class,
                    $payment->id
                );

                // Update existing payment.
                $payment->update([
                    'amount' => $amount,
                    'payment_date' => $request->payment_date,
                ]);

                // Post replacement journal using original ledger mappings.
                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Salary Advance Repayment - ' . $employee->name,

                    'entries' => [
                        [
                            'ledger_id' => $debitEntry->ledger_id,
                            'sub_ledger_id' => $debitEntry->sub_ledger_id,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => $creditEntry->ledger_id,
                            'sub_ledger_id' => $creditEntry->sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amount,
                        ],
                    ],
                ]);

                // Update payment journal ID.
                $payment->update([
                    'journal_entry_id' => $journal->id,
                ]);

                // Create replacement journal reference.
                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => SalaryAdvancePayment::class,
                    'model_id' => $payment->id,
                    'action' => 'updated',
                ]);

                // Recalculate salary advance status.
                $totalPaid = SalaryAdvancePayment::where(
                    'salary_advance_id',
                    $advance->id
                )->sum('amount');

                $advance->update([
                    'status' => $totalPaid >= $advance->amount
                        ? 'fully_returned'
                        : 'partial',
                ]);
            });

            return back()->with('success', 'Salary advance payment updated successfully.');
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', 'Unable to update the salary advance payment.');
        }
    }
}
