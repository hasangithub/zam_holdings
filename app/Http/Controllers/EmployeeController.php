<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\SalaryAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function salaryAdvanceIndex(Request $request)
    {
        $employees = Employee::orderBy('name')
            ->get();


        return view('salary-advances.index_employee', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        Employee::create($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee added successfully.');
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee->update($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()
                ->route('employees.index')
                ->with('error', 'This employee cannot be deleted because related records exist.');
        }
    }


    public function statement(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $all = $request->boolean('all');

        $fromDate = $request->input(
            'from_date',
            now()->subDays(6)->toDateString()
        );

        $toDate = $request->input('to_date', now()->toDateString());

        $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $ledger = collect();

        /*
    |--------------------------------------------------------------------------
    | OPENING BALANCE JOURNAL
    |--------------------------------------------------------------------------
    */

        if ($employee->opening_journal_entry_id) {
            $openingJournal = JournalEntry::find(
                $employee->opening_journal_entry_id
            );

            if ($openingJournal) {
                // Replace with your actual salary advance asset ledger ID.
              

                $openingAmount = (float) DB::table('journal_entry_details')
                    ->where('journal_entry_id', $openingJournal->id)
                    ->sum('debit');

                if ($openingAmount > 0) {
                    $ledger->push([
                        'date' => $openingJournal->journal_date,
                        'module' => 'Opening Balance',
                        'type' => 'Opening Balance',
                        'reference' => 'OPN-' . $openingJournal->id,
                        'description' => $openingJournal->description
                            ?? 'Employee Salary Advance Opening Balance',
                        'debit' => $openingAmount,
                        'credit' => 0,
                    ]);
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | SALARY ADVANCES ISSUED
    |--------------------------------------------------------------------------
    */

        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->where('branch_id', $employee->branch_id)
            ->where('status', '!=', 'cancelled')
            ->with('payments')
            ->get();

        foreach ($advances as $advance) {
            $ledger->push([
                'date' => $advance->advance_date,
                'module' => 'Salary Advance',
                'type' => 'Advance Issued',
                'reference' => 'ADV-' . $advance->id,
                'description' => $advance->description
                    ?? 'Salary advance issued',
                'debit' => (float) $advance->amount,
                'credit' => 0,
            ]);

            /*
        |--------------------------------------------------------------------------
        | REPAYMENTS
        |--------------------------------------------------------------------------
        */

            foreach ($advance->payments as $payment) {
                $ledger->push([
                    'date' => $payment->payment_date,
                    'module' => 'Salary Advance',
                    'type' => 'Repayment',
                    'reference' => $payment->reference
                        ?: 'PAY-' . $payment->id,
                    'description' => $payment->note
                        ?? 'Salary advance repayment',
                    'debit' => 0,
                    'credit' => (float) $payment->amount,
                ]);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | SORT TRANSACTIONS
    |--------------------------------------------------------------------------
    */

        $ledger = $ledger->sortBy(function ($row) {
            return [
                $row['date'],
                $row['type'],
                $row['reference'] ?? '',
            ];
        })->values();

        /*
    |--------------------------------------------------------------------------
    | DATE FILTER AND OPENING BALANCE FOR SELECTED PERIOD
    |--------------------------------------------------------------------------
    */

        $periodOpeningBalance = 0;
        $filtered = collect();

        if (!$all) {
            foreach ($ledger as $row) {
                if ($row['date'] < $fromDate) {
                    $periodOpeningBalance +=
                        $row['debit'] - $row['credit'];
                } elseif ($row['date'] <= $toDate) {
                    $filtered->push($row);
                }
            }

            $ledger = $filtered;
        }

        /*
    |--------------------------------------------------------------------------
    | RUNNING BALANCE
    |--------------------------------------------------------------------------
    */

        $running = $all ? 0 : $periodOpeningBalance;

        $ledger = $ledger->map(function ($row) use (&$running) {
            $running += $row['debit'] - $row['credit'];
            $row['balance'] = $running;

            return $row;
        });

        /*
    |--------------------------------------------------------------------------
    | TOTALS
    |--------------------------------------------------------------------------
    */

        $periodAdvances = $ledger->sum('debit');
        $periodRepayments = $ledger->sum('credit');

        $balance = ($all ? 0 : $periodOpeningBalance)
            + $periodAdvances
            - $periodRepayments;

        if ($all) {
            $balance = $ledger->last()['balance'] ?? 0;
        }

        return view('employees.statement', compact(
            'employee',
            'ledger',
            'balance',
            'periodOpeningBalance',
            'periodAdvances',
            'periodRepayments',
            'fromDate',
            'toDate',
            'all'
        ));
    }

    public function storeOpeningBalance(Request $request, Employee $employee)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
        ]);

        if ((int) $employee->branch_id !== (int) auth()->user()->branch_id) {
           // abort(403, 'You are not allowed to update this employee.');
        }

        DB::transaction(function () use ($request, $employee) {
            $lockedEmployee = Employee::whereKey($employee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEmployee->opening_journal_entry_id) {
                throw ValidationException::withMessages([
                    'amount' => 'Opening balance has already been entered for this employee.',
                ]);
            }


            $amount = (float) $request->amount;

            $journal = Accounting::postJournal([
                'branch_id' => 1,
                'date' => $request->date,
                'description' => 'Opening balance - Salary Advance: '
                    . $lockedEmployee->name,
                'created_by' => auth()->id(),
                'entries' => [
                    [
                        'ledger_id' => 17,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 2,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);

            // Accounting::postJournal() must return the created JournalEntry.
            $lockedEmployee->opening_journal_entry_id = $journal->id;
            $lockedEmployee->save();
        });

        return redirect()
            ->route('employees.statement', $employee->id)
            ->with('success', 'Employee opening balance saved successfully.');
    }
}
