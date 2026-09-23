<?php

namespace App\Http\Controllers;

use App\Models\SalaryAdvance;
use App\Models\SalaryAdvancePayment;
use App\Models\Employee;
use App\Models\Ledger;
use App\Models\SubLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $employees = User::where(
            'branch_id',
            auth()->user()->branch_id
        )
        ->orderBy('name')
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
            'employee_id' => [
                'required',
                'exists:users,id'
            ],

            'advance_date' => [
                'required',
                'date'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'payment_sub_ledger_id' => [
                'required',
                'exists:sub_ledgers,id'
            ],

            'description' => [
                'nullable',
                'string'
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        $employee = User::where('id', $request->employee_id)
            ->where(
                'branch_id',
                auth()->user()->branch_id
            )
            ->firstOrFail();

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
        | Employee Salary Advance Subledger
        |--------------------------------------------------------------------------
        */

       
        /*
        |--------------------------------------------------------------------------
        | Payment Account
        |--------------------------------------------------------------------------
        */

        $cashBankLedger = Ledger::where(
            'id',
            '1'
        )->firstOrFail();

        $paymentAccount = SubLedger::where(
            'id',
            $request->payment_sub_ledger_id
        )
        ->where(
            'ledger_id',
            $cashBankLedger->id
        )
        ->where(
            'branch_id',
            auth()->user()->branch_id
        )
    
        ->first();

        if (!$paymentAccount) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment_sub_ledger_id' =>
                        'Invalid payment account.'
                ]);
        }

        DB::transaction(function () use (
            $request,
            $paymentAccount
        ) {

            /*
            |--------------------------------------------------------------------------
            | Journal
            |--------------------------------------------------------------------------
            */

            $journal = JournalEntry::create([
                'branch_id' => auth()->user()->branch_id,

                'journal_date' => $request->advance_date,

                'description' =>
                    'Salary Advance - Employee',

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

                'ledger_id' =>
                    $paymentAccount->ledger_id,

                'sub_ledger_id' =>
                    $paymentAccount->id,

                'debit' => 0,

                'credit' => $request->amount,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Salary Advance
            |--------------------------------------------------------------------------
            */

            SalaryAdvance::create([
                'branch_id' =>
                    auth()->user()->branch_id,

                'user_id' =>
                    $request->employee_id,

                'advance_date' =>
                    $request->advance_date,

                'amount' =>
                    $request->amount,

                'salary_advance_sub_ledger_id' =>
                    1,

                'payment_sub_ledger_id' =>
                    $paymentAccount->id,

                'journal_entry_id' =>
                    $journal->id,

                'status' =>
                    'pending',

                'description' =>
                    $request->description,

                'created_by' =>
                    auth()->id(),
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

        $cashBankLedger = Ledger::where(
            'id',
            1
        )->firstOrFail();

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

        $returnedAmount =
            $salaryAdvance->payments()->sum('amount');

        $outstanding =
            $salaryAdvance->amount - $returnedAmount;

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

        /*
        |--------------------------------------------------------------------------
        | Salary Advance Account
        |--------------------------------------------------------------------------
        */

        $salaryAdvanceSubLedger =
            SubLedger::findOrFail(
                $salaryAdvance->salary_advance_sub_ledger_id
            );

        /*
        |--------------------------------------------------------------------------
        | Payment Account Security
        |--------------------------------------------------------------------------
        */

        $cashBankLedger = Ledger::where(
            'id',
            1
        )->firstOrFail();

        $paymentAccount = SubLedger::where(
            'id',
            $request->payment_sub_ledger_id
        )
        ->where(
            'ledger_id',
            $cashBankLedger->id
        )
        ->where(
            'branch_id',
            auth()->user()->branch_id
        )
       
        ->first();

        if (!$paymentAccount) {
            return back()->withErrors([
                'payment_sub_ledger_id' =>
                    'Invalid payment account.'
            ]);
        }

        DB::transaction(function () use (
            $request,
            $salaryAdvance,
            $salaryAdvanceSubLedger,
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
                'journal_entry_id' =>
                    $journal->id,

                'ledger_id' =>
                    $paymentAccount->ledger_id,

                'sub_ledger_id' =>
                    $paymentAccount->id,

                'debit' =>
                    $request->amount,

                'credit' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cr Salary Advance
            |--------------------------------------------------------------------------
            */

            JournalEntryDetail::create([
                'journal_entry_id' =>
                    $journal->id,

                'ledger_id' =>
                    $salaryAdvanceSubLedger->ledger_id,

                'sub_ledger_id' =>
                    $salaryAdvanceSubLedger->id,

                'debit' => 0,

                'credit' =>
                    $request->amount,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            SalaryAdvancePayment::create([
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
}