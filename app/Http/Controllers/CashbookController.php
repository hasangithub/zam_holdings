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
        $cashBankLedger = Ledger::where('id', '1')
            ->firstOrFail();

        // Only current user's cashbooks in current branch
        $cashBooks = SubLedger::where('ledger_id', $cashBankLedger->id)
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        $cashbookId = $request->cashbook_sub_ledger_id;

        // Default to first available cashbook
        if (!$cashbookId && $cashBooks->count()) {
            $cashbookId = $cashBooks->first()->id;
        }

        $transactions = collect();

        $totalCashIn = 0;
        $totalCashOut = 0;

        if ($cashbookId) {

            // Security: selected cashbook MUST belong
            // to current branch and current user
            $cashbook = $cashBooks->firstWhere('id', $cashbookId);

            if (!$cashbook) {
                abort(403, 'Invalid cashbook selected.');
            }

            $query = CashbookTransaction::with([
                'ledger',
                'subLedger',
            ])
                ->where('branch_id', auth()->user()->branch_id)
                ->where('cashbook_sub_ledger_id', $cashbookId);

            if ($request->filled('from_date')) {
                $query->whereDate(
                    'transaction_date',
                    '>=',
                    $request->from_date
                );
            }

            if ($request->filled('to_date')) {
                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $request->to_date
                );
            }

            $transactions = $query
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

        return view('cashbooks.index', compact(
            'cashBooks',
            'transactions',
            'cashbookId',
            'totalCashIn',
            'totalCashOut'
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

        // All ledgers except Cash & Bank
        $ledgers = Ledger::where('id', '!=', '1')
            ->orderBy('name')
            ->get();

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
        if ($ledger->id === 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'ledger_id' => 'Cash / Bank cannot be selected as the other account.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Other Subledger
        |--------------------------------------------------------------------------
        */

        $subLedger = null;

        if ($request->filled('sub_ledger_id')) {

            $subLedger = SubLedger::where('id', $request->sub_ledger_id)
                ->where('ledger_id', $ledger->id)
                ->whereNull('branch_id')
                ->first();

            if (!$subLedger) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'sub_ledger_id' =>
                        'Selected subledger does not belong to the selected ledger.'
                    ]);
            }
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
}
