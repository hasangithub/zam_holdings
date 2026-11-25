<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting as AppAccounting;
use App\Models\Cost;
use App\Models\CostCategory;
use App\Models\CostSubCategory;
use App\Models\CostPayment;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Ledger;
use App\Models\SubLedger;
use App\Models\JournalEntryReference;
use App\Models\JournalEntry;
use App\Models\Accounting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CostController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    /*
    |--------------------------------------------------------------------------
    | COST LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $branchId = $this->branchId();

        $query = Cost::with([
            'category',
            'subCategory',
            'payments',
        ])->where('branch_id', $branchId);

        if ($request->filled('from_date')) {
            $query->whereDate('cost_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('cost_date', '<=', $request->to_date);
        }

        if ($request->filled('category_id')) {
            $query->where('cost_category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('cost_sub_category_id', $request->sub_category_id);
        }

        $costs = $query->latest('cost_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $categories = CostCategory::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('costs.index', compact('costs', 'categories'));
    }

    public function indexCostCategory(Request $request)
    {
        $branchId = $this->branchId();

        $costSubCategories = CostSubCategory::with(['category'])->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('costs.index-cost-category', compact('costSubCategories'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $categories = CostCategory::where('branch_id', $this->branchId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Cashbook subledgers belong to ledger ID 1.
        $cashbooks = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('costs.create', compact('categories', 'cashbooks'));
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD SUBCATEGORIES
    |--------------------------------------------------------------------------
    */

    public function getSubCategories($categoryId)
    {
        $subCategories = CostSubCategory::where(
            'branch_id',
            $this->branchId()
        )
            ->where('cost_category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subCategories);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE CATEGORY
    |--------------------------------------------------------------------------
    */

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        CostCategory::create([
            'branch_id' => $this->branchId(),
            'name' => $data['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Cost category created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE SUBCATEGORY AND ACCOUNTING LEDGERS
    |--------------------------------------------------------------------------
    */

    public function storeSubCategory(Request $request)
    {
        $data = $request->validate([
            'cost_category_id' => 'required|exists:cost_categories,id',
            'name' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data) {

            $branchId = $this->branchId();

            $category = CostCategory::where('branch_id', $branchId)
                ->where('id', $data['cost_category_id'])
                ->firstOrFail();

            $expenseType = AccountType::where('name', 'Expenses')
                ->firstOrFail();

            $operatingExpenses = AccountGroup::where(
                'account_type_id',
                $expenseType->id
            )
                ->where('name', 'Operating Expenses')
                ->firstOrFail();

            $liabilityType = AccountType::where('name', 'Liability')
                ->firstOrFail();

            $currentLiability = AccountGroup::where(
                'account_type_id',
                $liabilityType->id
            )
                ->where('name', 'Current Liabilities')
                ->firstOrFail();

            $accruedExpense = Ledger::where(
                'account_group_id',
                $currentLiability->id
            )
                ->where('name', 'Accrued Expenses')
                ->firstOrFail();

            // Create expense ledger for this subcategory.
            $ledger = Ledger::create([
                'account_group_id' => $operatingExpenses->id,
                'name' => $data['name'],
            ]);

            // Create accrued expense subledger.
            $accruedSubLedger = SubLedger::create([
                'ledger_id' => $accruedExpense->id,
                'name' => $data['name'],
            ]);

            CostSubCategory::create([
                'branch_id' => $branchId,
                'cost_category_id' => $category->id,
                'name' => $data['name'],
                'ledger_id' => $ledger->id,
                'accrued_expense_sub_ledger_id' => $accruedSubLedger->id,
                'is_active' => true,
            ]);
        });

        return back()->with('success', 'Subcategory and accounting ledgers created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE COST
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'cost_category_id' => 'required|exists:cost_categories,id',
            'cost_sub_category_id' => 'required|exists:cost_sub_categories,id',
            'cost_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'payment_status' => 'required|in:paid,unpaid',
            'payment_amount' => 'nullable|numeric|min:0',
            'cashbook_sub_ledger_id' => 'nullable|exists:sub_ledgers,id',
            'note' => 'nullable|string|max:2000',
            'payment_date' => 'nullable|date',
            'payment_note' => 'nullable|string|max:1000',
        ]);

        $branchId = $this->branchId();

        $amount = round((float) $data['amount'], 2);

        $paymentAmount = 0;

        if ($data['payment_status'] === 'paid') {
            $paymentAmount = $amount;
        }

        if ($data['payment_status'] === 'unpaid' && ($data['payment_amount'] ?? 0) > 0) {
            throw ValidationException::withMessages([
                'payment_amount' => 'Payment amount must be zero for an unpaid cost.',
            ]);
        }

        if ($paymentAmount > 0) {
            $request->validate([
                'cashbook_sub_ledger_id' => 'required|exists:sub_ledgers,id',
            ]);

            $cashbook = SubLedger::where('id', $data['cashbook_sub_ledger_id'])
                ->where('ledger_id', 1)
                ->first();

            if (!$cashbook) {
                throw ValidationException::withMessages([
                    'cashbook_sub_ledger_id' => 'Please select a valid cashbook.',
                ]);
            }
        }

        DB::transaction(function () use (
            $data,
            $branchId,
            $amount,
            $paymentAmount
        ) {
            $category = CostCategory::where('branch_id', $branchId)
                ->where('id', $data['cost_category_id'])
                ->firstOrFail();

            $subCategory = CostSubCategory::where('branch_id', $branchId)
                ->where('cost_category_id', $category->id)
                ->where('id', $data['cost_sub_category_id'])
                ->firstOrFail();

            // Create main cost record.
            $cost = Cost::create([
                'branch_id' => $branchId,
                'cost_category_id' => $category->id,
                'cost_sub_category_id' => $subCategory->id,
                'cost_date' => $data['cost_date'],
                'amount' => $amount,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            /*
             * Journal 1:
             * Debit expense ledger.
             * Credit accrued expense subledger.
             *
             * Always record the FULL cost first.
             */

            $journal = AppAccounting::postJournal([
                'branch_id' => $branchId,
                'date' => $data['cost_date'],
                'description' => 'Cost #' . $cost->id . ' - ' . $subCategory->name,

                'entries' => [
                    [
                        'ledger_id' => $subCategory->ledger_id,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 9,
                        'sub_ledger_id' => $subCategory->accrued_expense_sub_ledger_id,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => Cost::class,
                'model_id' => $cost->id,
                'action' => 'created',
            ]);

            // If paid now, create the payment and payment journal.
            if ($paymentAmount > 0) {
                $this->createCostPayment(
                    $cost,
                    $subCategory,
                    $paymentAmount,
                    $data['cashbook_sub_ledger_id'],
                    $data['payment_date'] ?? $data['cost_date'],
                    $data['payment_note'] ?? null
                );
            }
        });

        return redirect()
            ->route('costs.index')
            ->with('success', 'Cost created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | COST STATEMENT
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $cost = Cost::with([
            'category',
            'subCategory',
            'payments.cashbookSubLedger',
        ])
            ->where('branch_id', $this->branchId())
            ->findOrFail($id);

        $cashbooks = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('costs.show', compact('cost', 'cashbooks'));
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PAYMENT
    |--------------------------------------------------------------------------
    */

    public function storePayment(Request $request, $id)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'cashbook_sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $id) {

            $cost = Cost::where('branch_id', $this->branchId())
                ->lockForUpdate()
                ->findOrFail($id);

            $subCategory = CostSubCategory::where('branch_id', $this->branchId())
                ->findOrFail($cost->cost_sub_category_id);

            $paidAmount = (float) $cost->payments()->sum('amount');
            $balance = round((float) $cost->amount - $paidAmount, 2);

            $paymentAmount = round((float) $data['amount'], 2);

            if ($paymentAmount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the outstanding balance of ' .
                        number_format($balance, 2),
                ]);
            }

            $cashbook = SubLedger::where('id', $data['cashbook_sub_ledger_id'])
                ->where('ledger_id', 1)
                ->first();

            if (!$cashbook) {
                throw ValidationException::withMessages([
                    'cashbook_sub_ledger_id' => 'Please select a valid cashbook.',
                ]);
            }

            $this->createCostPayment(
                $cost,
                $subCategory,
                $paymentAmount,
                $cashbook->id,
                $data['payment_date'],
                $data['note'] ?? null
            );
        });

        return back()->with('success', 'Cost payment recorded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHARED PAYMENT CREATION
    |--------------------------------------------------------------------------
    */

    private function createCostPayment(
        Cost $cost,
        CostSubCategory $subCategory,
        float $amount,
        int $cashbookSubLedgerId,
        string $paymentDate,
        ?string $note
    ) {
        $payment = CostPayment::create([
            'cost_id' => $cost->id,
            'branch_id' => $cost->branch_id,
            'payment_date' => $paymentDate,
            'amount' => $amount,
            'cashbook_sub_ledger_id' => $cashbookSubLedgerId,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        /*
         * Debit accrued expense.
         * Credit selected cashbook.
         */

        $journal = AppAccounting::postJournal([
            'branch_id' => $cost->branch_id,
            'date' => $paymentDate,
            'description' => 'Cost Payment #' . $payment->id . ' - Cost #' . $cost->id,

            'entries' => [
                [
                    'ledger_id' => 9,
                    'sub_ledger_id' => $subCategory->accrued_expense_sub_ledger_id,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'ledger_id' => 1,
                    'sub_ledger_id' => $cashbookSubLedgerId,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ],
        ]);

        JournalEntryReference::create([
            'journal_entry_id' => $journal->id,
            'model_type' => CostPayment::class,
            'model_id' => $payment->id,
            'action' => 'created',
        ]);

        return $payment;
    }


    public function statementSubCategory(Request $request, $id)
    {
        $branchId = auth()->user()->branch_id;

        $subCategory = CostSubCategory::where('branch_id', $branchId)
            ->with(['category', 'openingJournalEntry'])
            ->findOrFail($id);

        $all = $request->boolean('all');

        $fromDate = $request->input(
            'from_date',
            now()->subDays(6)->toDateString()
        );

        $toDate = $request->input(
            'to_date',
            now()->toDateString()
        );

        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $fromDate = \Carbon\Carbon::parse($fromDate)->toDateString();
        $toDate = \Carbon\Carbon::parse($toDate)->toDateString();

        /*
    |--------------------------------------------------------------------------
    | Get all costs for this subcategory
    |--------------------------------------------------------------------------
    */

        $costs = Cost::where('branch_id', $branchId)
            ->where('cost_sub_category_id', $subCategory->id)
            ->orderBy('cost_date')
            ->orderBy('id')
            ->get();

        $costIds = $costs->pluck('id');

        /*
    |--------------------------------------------------------------------------
    | Build complete statement
    |--------------------------------------------------------------------------
    */

        $completeLedger = collect();

        /*
|--------------------------------------------------------------------------
| Opening Balance
|--------------------------------------------------------------------------
*/

        if ($subCategory->openingJournalEntry) {

            $openingJournal = $subCategory->openingJournalEntry;

            $openingAmount = (float) \App\Models\JournalEntryDetail::where(
                'journal_entry_id',
                $openingJournal->id
            )
                ->where('ledger_id', $subCategory->ledger_id)
                ->sum('debit');

            if ($openingAmount > 0) {

                $completeLedger->push([

                    'date' => \Carbon\Carbon::parse(
                        $openingJournal->journal_date
                    )->toDateString(),

                    'module' => 'Cost Management',

                    'reference' => 'OPN-' . $openingJournal->id,

                    'type' => 'Opening Balance',

                    'description' => $openingJournal->description
                        ?: 'Opening Balance - ' . $subCategory->name,

                    'debit' => $openingAmount,

                    'credit' => 0,

                    'sequence' => 0,

                    'id' => $openingJournal->id,

                ]);
            }
        }

        // Cost entries
        foreach ($costs as $cost) {

            $completeLedger->push([
                'date' => \Carbon\Carbon::parse($cost->cost_date)->toDateString(),

                'module' => 'Cost Management',

                'reference' => 'Cost #' . $cost->id,

                'type' => 'Cost',

                'description' => $cost->note ?: 'Cost Entry',

                'debit' => (float) $cost->amount,

                'credit' => 0,

                'sequence' => 1,

                'id' => $cost->id,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Cost payments
    |--------------------------------------------------------------------------
    */

        $payments = CostPayment::where('branch_id', $branchId)
            ->whereIn('cost_id', $costIds)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {

            $completeLedger->push([
                'date' => \Carbon\Carbon::parse($payment->payment_date)->toDateString(),

                'module' => 'Cost Management',

                'reference' => 'Payment #' . $payment->id
                    . ' / Cost #' . $payment->cost_id,

                'type' => 'Payment',

                'description' => $payment->note ?: 'Cost Payment',

                'debit' => 0,

                'credit' => (float) $payment->amount,

                'sequence' => 2,

                'id' => $payment->id,

                'payment_id' => $payment->id,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Sort complete history
    |--------------------------------------------------------------------------
    */

        $completeLedger = $completeLedger
            ->sortBy([
                ['date', 'asc'],
                ['sequence', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        /*
    |--------------------------------------------------------------------------
    | Calculate running balance
    |--------------------------------------------------------------------------
    */

        $runningBalance = 0;

        $completeLedger = $completeLedger->map(function ($row) use (&$runningBalance) {

            $runningBalance += $row['debit'] - $row['credit'];

            $row['balance'] = $runningBalance;

            return $row;
        });

        /*
    |--------------------------------------------------------------------------
    | Apply date filtering
    |--------------------------------------------------------------------------
    */

        if ($all) {

            $ledger = $completeLedger->values();

            $openingBalance = 0;
        } else {

            $openingBalance = (float) $completeLedger
                ->filter(function ($row) use ($fromDate) {
                    return $row['date'] < $fromDate;
                })
                ->sum(function ($row) {
                    return $row['debit'] - $row['credit'];
                });

            $ledger = $completeLedger
                ->filter(function ($row) use ($fromDate, $toDate) {
                    return $row['date'] >= $fromDate
                        && $row['date'] <= $toDate;
                })
                ->values();
        }

        /*
    |--------------------------------------------------------------------------
    | Period totals and outstanding balance
    |--------------------------------------------------------------------------
    */

        $periodCost = (float) $ledger->sum('debit');

        $periodPaid = (float) $ledger->sum('credit');

        $balance = $all
            ? (float) ($ledger->last()['balance'] ?? 0)
            : $openingBalance + $periodCost - $periodPaid;

        /*
    |--------------------------------------------------------------------------
    | Recalculate displayed running balances
    |--------------------------------------------------------------------------
    */

        if (!$all) {

            $runningBalance = $openingBalance;

            $ledger = $ledger->map(function ($row) use (&$runningBalance) {

                $runningBalance += $row['debit'] - $row['credit'];

                $row['balance'] = $runningBalance;

                return $row;
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Payment accounts
    |--------------------------------------------------------------------------
    */

        $paymentSubLedgers = SubLedger::where('ledger_id', 1)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        return view('costs.statement_sub_category', compact(
            'subCategory',
            'costs',
            'payments',
            'ledger',
            'all',
            'fromDate',
            'toDate',
            'openingBalance',
            'periodCost',
            'periodPaid',
            'balance',
            'paymentSubLedgers'
        ));
    }

    public function storeOpeningBalance(Request $request, $id)
    {
        $branchId = auth()->user()->branch_id;

        $data = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $id, $branchId) {

            $subCategory = CostSubCategory::where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($subCategory->opening_journal_entry_id) {
                throw ValidationException::withMessages([
                    'amount' => 'An opening balance has already been recorded for this subcategory.',
                ]);
            }

            $amount = (float) $data['amount'];

            /*
        |--------------------------------------------------------------------------
        | Opening Balance Journal
        |--------------------------------------------------------------------------
        */

            $journal = AppAccounting::postJournal([
                'branch_id' => $branchId,
                'date' => $data['date'],
                'description' => $data['description']
                    ?: 'Opening Balance - ' . $subCategory->name,

                'entries' => [

                    // Debit: Subcategory expense ledger
                    [
                        'ledger_id' => $subCategory->ledger_id,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],

                    // Credit: Accrued Expenses
                    [
                        'ledger_id' => 9,
                        'sub_ledger_id' => $subCategory->accrued_expense_sub_ledger_id,
                        'debit' => 0,
                        'credit' => $amount,
                    ],

                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | Save Journal Reference
        |--------------------------------------------------------------------------
        */

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => CostSubCategory::class,
                'model_id' => $subCategory->id,
                'action' => 'created',
            ]);

            /*
        |--------------------------------------------------------------------------
        | Save Opening Journal ID
        |--------------------------------------------------------------------------
        */

            $subCategory->update([
                'opening_journal_entry_id' => $journal->id,
            ]);
        });

        return redirect()
            ->route('costs.sub-categories.statement', $id)
            ->with('success', 'Opening balance saved successfully.');
    }

    public function updateCost(Request $request, $id)
    {
        $branchId = auth()->user()->branch_id;

        $data = $request->validate([
            'cost_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'note' => 'nullable|string',
        ]);

        $cost = Cost::where('branch_id', $branchId)
            ->lockForUpdate()
            ->findOrFail($id);

        DB::transaction(function () use ($data, $cost, $branchId) {



            $subCategory = CostSubCategory::where('branch_id', $branchId)
                ->findOrFail($cost->cost_sub_category_id);

            // Total payments already made
            $paidAmount = CostPayment::where('cost_id', $cost->id)
                ->where('branch_id', $branchId)
                ->sum('amount');

            // Cost cannot be less than payments already made
            if ((float) $data['amount'] < (float) $paidAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'Cost amount cannot be less than the payments already made: '
                        . number_format($paidAmount, 2),
                ]);
            }

            // Find original cost journal
            $reference = JournalEntryReference::where(
                'model_type',
                Cost::class
            )
                ->where('model_id', $cost->id)
                ->latest('id')
                ->firstOrFail();

            $journal = JournalEntry::where('branch_id', $branchId)
                ->findOrFail($reference->journal_entry_id);

            $amount = (float) $data['amount'];

            // Update cost journal header
            $journal->forceFill([
                'journal_date' => $data['cost_date'],
                'description' => $data['note']
                    ?: 'Cost - ' . $subCategory->name,
            ])->save();

            // Update debit: Subcategory expense ledger
            $debitUpdated = DB::table('journal_entry_details')
                ->where('journal_entry_id', $journal->id)
                ->where('ledger_id', $subCategory->ledger_id)
                ->whereNull('sub_ledger_id')
                ->update([
                    'debit' => $amount,
                    'credit' => 0,
                ]);

            // Update credit: Accrued expenses
            $creditUpdated = DB::table('journal_entry_details')
                ->where('journal_entry_id', $journal->id)
                ->where('ledger_id', 9)
                ->where('sub_ledger_id', $subCategory->accrued_expense_sub_ledger_id)
                ->update([
                    'debit' => 0,
                    'credit' => $amount,
                ]);

            if (!$debitUpdated || !$creditUpdated) {
                throw new \RuntimeException(
                    'The original cost journal entries could not be found.'
                );
            }

            // Update cost record
            $cost->update([
                'cost_date' => $data['cost_date'],
                'amount' => $amount,
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()
            ->route('costs.sub-categories.statement', $cost->cost_sub_category_id)
            ->with('success', 'Cost updated successfully.');
    }

    public function updatePayment(Request $request, $paymentId)
    {
        $branchId = auth()->user()->branch_id;

        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'cashbook_sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => 'nullable|string',
        ]);

        $payment = CostPayment::where('branch_id', $branchId)
            ->with('cost')
            ->findOrFail($paymentId);

        $costId = $payment->cost_id;

        DB::transaction(function () use (
            $data,
            $paymentId,
            $branchId,
            $costId
        ) {

            $cost = Cost::where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($costId);

            $payment = CostPayment::where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($paymentId);

            // Validate selected cashbook belongs to this branch and ledger 1
            $cashbook = SubLedger::where('branch_id', $branchId)
                ->where('ledger_id', 1)
                ->findOrFail($data['cashbook_sub_ledger_id']);

            // Calculate other payments, excluding the payment being edited
            $otherPayments = CostPayment::where('branch_id', $branchId)
                ->where('cost_id', $cost->id)
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $availableBalance = (float) $cost->amount
                - (float) $otherPayments;

            if ((float) $data['amount'] > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the available balance of '
                        . number_format($availableBalance, 2),
                ]);
            }

            $subCategory = CostSubCategory::where('branch_id', $branchId)
                ->findOrFail($cost->cost_sub_category_id);

            // Find original payment journal
            $reference = JournalEntryReference::where(
                'model_type',
                CostPayment::class
            )
                ->where('model_id', $payment->id)
                ->latest('id')
                ->firstOrFail();

            $journal = JournalEntry::where('branch_id', $branchId)
                ->findOrFail($reference->journal_entry_id);

            $amount = (float) $data['amount'];

            // Update journal header
            $journal->forceFill([
                'journal_date' => $data['payment_date'],
                'description' => $data['note']
                    ?: 'Cost Payment - ' . $subCategory->name,
            ])->save();

            // Update debit: Accrued expenses
            $debitUpdated = DB::table('journal_entry_details')
                ->where('journal_entry_id', $journal->id)
                ->where('ledger_id', 9)
                ->where('sub_ledger_id', $subCategory->accrued_expense_sub_ledger_id)
                ->update([
                    'debit' => $amount,
                    'credit' => 0,
                ]);

            // Update credit: Cash / Bank
            $creditUpdated = DB::table('journal_entry_details')
                ->where('journal_entry_id', $journal->id)
                ->where('ledger_id', 1)
                ->update([
                    'ledger_id' => 1,
                    'sub_ledger_id' => $cashbook->id,
                    'debit' => 0,
                    'credit' => $amount,
                ]);

            if (!$debitUpdated || !$creditUpdated) {
                throw new \RuntimeException(
                    'The original payment journal entries could not be found.'
                );
            }

            // Update payment record
            $payment->update([
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'cashbook_sub_ledger_id' => $cashbook->id,
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()
            ->route('costs.sub-categories.statement', $payment->cost->cost_sub_category_id)
            ->with('success', 'Payment updated successfully.');
    }
}
