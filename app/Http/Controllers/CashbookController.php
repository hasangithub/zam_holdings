<?php

namespace App\Http\Controllers;

use App\Models\CashbookTransaction;
use App\Models\Ledger;
use App\Models\SubLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashbookController extends Controller
{
    /**
     * Cash Book T-form
     */
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $userId = auth()->id();

        $request->validate([
            'cashbook_sub_ledger_id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        /*
    |--------------------------------------------------------------------------
    | Cash Books
    |--------------------------------------------------------------------------
    | Only cash/bank subledgers assigned to this branch and user.
    */
        $cashBooks = SubLedger::with('ledger')
            ->where('branch_id', $branchId)
            ->whereHas('ledger.accountGroup', function ($query) {
                $query->where('id', '1');
            })
            ->orderBy('name')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Selected Cash Book
    |--------------------------------------------------------------------------
    */
        $cashbookId = $request->input('cashbook_sub_ledger_id');

        if ($cashbookId) {
            // Ensure selected cashbook belongs to this user and branch.
            $selectedCashBook = $cashBooks->firstWhere('id', $cashbookId);

            if (!$selectedCashBook) {
                abort(403, 'You are not authorized to access this cash book.');
            }
        } else {
            $cashbookId = optional($cashBooks->first())->id;
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        /*
    |--------------------------------------------------------------------------
    | Cash Book Transactions
    |--------------------------------------------------------------------------
    */
        $transactions = collect();

        $totalCashIn = 0;
        $totalCashOut = 0;

        if ($cashbookId) {

            $transactionQuery = CashbookTransaction::with([
                'ledger',
                'subLedger',
                'creator',
                'journalEntry',
            ])
                ->where('branch_id', $branchId)
                ->where('cashbook_sub_ledger_id', $cashbookId)
                ->when($fromDate, function ($query) use ($fromDate) {
                    $query->whereDate('transaction_date', '>=', $fromDate);
                })
                ->when($toDate, function ($query) use ($toDate) {
                    $query->whereDate('transaction_date', '<=', $toDate);
                });

            $transactions = $transactionQuery
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            $totalCashIn = $transactions
                ->where('type', 'in')
                ->sum('amount');

            $totalCashOut = $transactions
                ->where('type', 'out')
                ->sum('amount');
        }

        /*
    |--------------------------------------------------------------------------
    | Journal Summary
    |--------------------------------------------------------------------------
    */
        $journalSummary = [
            'openingBalance' => 0,
            'journals' => collect(),
            'totalDebit' => 0,
            'totalCredit' => 0,
            'closingBalance' => 0,
        ];

        if ($cashbookId) {
            $journalSummary = $this->getCashBookJournalSummary(
                (int) $cashbookId,
                (int) $branchId,
                $fromDate,
                $toDate
            );
        }

        $journalOpeningBalance = $journalSummary['openingBalance'];
        $journalEntries = $journalSummary['journals'];
        $journalTotalDebit = $journalSummary['totalDebit'];
        $journalTotalCredit = $journalSummary['totalCredit'];
        $journalClosingBalance = $journalSummary['closingBalance'];

        return view('cashbooks.index', compact(
            'cashBooks',
            'cashbookId',
            'transactions',
            'totalCashIn',
            'totalCashOut',
            'journalOpeningBalance',
            'journalEntries',
            'journalTotalDebit',
            'journalTotalCredit',
            'journalClosingBalance'
        ));
    }

    public function subLedgers($ledgerId)
    {
        $ledger = Ledger::findOrFail($ledgerId);

        /*
        |--------------------------------------------------------------------------
        | Get subledgers belonging to selected ledger
        |--------------------------------------------------------------------------
        */

        $subLedgers = SubLedger::where('ledger_id', $ledger->id)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($subLedgers);
    }


    /**
     * Create Cash Book transaction
     */
    public function create()
    {
        $cashBankLedger = Ledger::where('id', '1')
            ->firstOrFail();

        // Current user's cashbooks only
        $cashBooks = SubLedger::where('ledger_id', $cashBankLedger->id)
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        // All ledgers 
        $ledgers = Ledger::orderBy('name')->get();

        // All global subledgers
        // Cashbook subledgers are excluded.
        $subLedgers = SubLedger::where(function ($query) {
            $query->whereNull('branch_id');
        })
            ->orderBy('name')
            ->get();

        return view('cashbooks.create', compact(
            'cashBooks',
            'ledgers',
            'subLedgers'
        ));
    }


    /**
     * Store Cash In / Cash Out
     */
    public function store(Request $request)
    {


        $request->validate([
            'cashbook_sub_ledger_id' => [
                'required',
                'integer',
            ],

            'transaction_date' => [
                'required',
                'date',
            ],

            'type' => [
                'required',
                'in:in,out',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'ledger_id' => [
                'required',
                'integer',
                'exists:ledgers,id',
            ],

            'sub_ledger_id' => [
                'nullable',
                'integer',
                'exists:sub_ledgers,id',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Cashbook
        |--------------------------------------------------------------------------
        */

        $cashBankLedger = Ledger::where('id', '1')
            ->firstOrFail();

        $cashbook = SubLedger::where('id', $request->cashbook_sub_ledger_id)
            ->where('ledger_id', $cashBankLedger->id)
            ->where('branch_id', auth()->user()->branch_id)
            ->first();

        if (!$cashbook) {
            abort(403, 'Invalid cashbook selected.');
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Other Ledger
        |--------------------------------------------------------------------------
        */

        $ledger = Ledger::findOrFail($request->ledger_id);

        // Do not allow another CASH_BANK account
        // if ($ledger->id === 1) {
        //     return back()
        //         ->withInput()
        //         ->withErrors([
        //             'ledger_id' => 'Cash / Bank cannot be selected as the other account.'
        //         ]);
        // }


        /*
        |--------------------------------------------------------------------------
        | Validate Other Subledger
        |--------------------------------------------------------------------------
        */

        $subLedger = null;

        if ($request->filled('sub_ledger_id')) {

            $subLedger = SubLedger::where('id', $request->sub_ledger_id)
                ->where('ledger_id', $ledger->id)
                ->first();

            // if (!$subLedger) {
            //     return back()
            //         ->withInput()
            //         ->withErrors([
            //             'sub_ledger_id' =>
            //             'Selected subledger does not belong to the selected ledger.'
            //         ]);
            // }
        }


        /*
        |--------------------------------------------------------------------------
        | Create Transaction + Journal
        |--------------------------------------------------------------------------
        */



        DB::transaction(function () use (
            $request,
            $cashbook,
            $ledger,
            $subLedger
        ) {

            /*
            |--------------------------------------------------------------------------
            | Journal Header
            |--------------------------------------------------------------------------
            */

            $journal = JournalEntry::create([
                'branch_id' => auth()->user()->branch_id,
                'journal_date' => $request->transaction_date,
                'description' => $request->description
                    ?: ($request->type === 'in'
                        ? 'Cash Book - Cash In'
                        : 'Cash Book - Cash Out'),
                'created_by' => auth()->id(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Cash In
            |
            | Dr Cashbook
            | Cr Other Account
            |--------------------------------------------------------------------------
            */

            if ($request->type === 'in') {

                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'ledger_id' => $cashbook->ledger_id,
                    'sub_ledger_id' => $cashbook->id,
                    'debit' => $request->amount,
                    'credit' => 0,
                ]);

                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'ledger_id' => $ledger->id,
                    'sub_ledger_id' => $subLedger?->id,
                    'debit' => 0,
                    'credit' => $request->amount,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Cash Out
            |
            | Dr Other Account
            | Cr Cashbook
            |--------------------------------------------------------------------------
            */ else {

                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'ledger_id' => $ledger->id,
                    'sub_ledger_id' => $subLedger?->id,
                    'debit' => $request->amount,
                    'credit' => 0,
                ]);

                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'ledger_id' => $cashbook->ledger_id,
                    'sub_ledger_id' => $cashbook->id,
                    'debit' => 0,
                    'credit' => $request->amount,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Cashbook Transaction
            |--------------------------------------------------------------------------
            */

            CashbookTransaction::create([
                'branch_id' => auth()->user()->branch_id,
                'cashbook_sub_ledger_id' => $cashbook->id,
                'transaction_date' => $request->transaction_date,
                'type' => $request->type,
                'amount' => $request->amount,
                'ledger_id' => $ledger->id,
                'sub_ledger_id' => $subLedger?->id,
                'reference' => $request->reference,
                'description' => $request->description,
                'journal_entry_id' => $journal->id,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('cashbooks.index')
            ->with('success', 'Cash book transaction recorded successfully.');
    }

    private function getCashBookJournalSummary(
        int $cashbookId,
        int $branchId,
        ?string $fromDate = null,
        ?string $toDate = null
    ) {
        /*
    |--------------------------------------------------------------------------
    | Validate Cash Book
    |--------------------------------------------------------------------------
    */
        $cashBook = SubLedger::where('branch_id', $branchId)
            ->findOrFail($cashbookId);

        /*
    |--------------------------------------------------------------------------
    | Base Journal Query
    |--------------------------------------------------------------------------
    */
        $baseQuery = DB::table('journal_entry_details as jed')
            ->join(
                'journal_entries as je',
                'je.id',
                '=',
                'jed.journal_entry_id'
            )
            ->where('je.branch_id', $branchId)
            ->where('jed.sub_ledger_id', $cashBook->id);

        /*
    |--------------------------------------------------------------------------
    | Opening Balance
    |--------------------------------------------------------------------------
    | All journal movements before From Date.
    */
        $openingBalance = 0;

        if ($fromDate) {

            $opening = (clone $baseQuery)
                ->whereDate('je.journal_date', '<', $fromDate)
                ->selectRaw('
                COALESCE(SUM(jed.debit), 0) as total_debit,
                COALESCE(SUM(jed.credit), 0) as total_credit
            ')
                ->first();

            $openingBalance =
                (float) $opening->total_debit -
                (float) $opening->total_credit;
        }

        /*
    |--------------------------------------------------------------------------
    | Journal Entries
    |--------------------------------------------------------------------------
    */
        $journals = (clone $baseQuery)
            ->when($fromDate, function ($query) use ($fromDate) {
                $query->whereDate('je.journal_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                $query->whereDate('je.journal_date', '<=', $toDate);
            })
            ->select([
                'je.id as journal_entry_id',
                'je.journal_date',
                'je.description',
                'je.created_by',
                'jed.id as detail_id',
                'jed.ledger_id',
                'jed.sub_ledger_id',
                'jed.debit',
                'jed.credit',
            ])
            ->orderBy('je.journal_date')
            ->orderBy('je.id')
            ->orderBy('jed.id')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Running Balance
    |--------------------------------------------------------------------------
    */
        $runningBalance = $openingBalance;

        foreach ($journals as $journal) {

            $debit = (float) $journal->debit;
            $credit = (float) $journal->credit;

            $runningBalance += $debit - $credit;

            $journal->running_balance = $runningBalance;
        }

        /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */
        $totalDebit = (float) $journals->sum('debit');
        $totalCredit = (float) $journals->sum('credit');

        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return [
            'openingBalance' => $openingBalance,
            'journals' => $journals,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $closingBalance,
        ];
    }
}
