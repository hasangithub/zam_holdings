<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Freight;
use App\Models\FreightPayment;
use App\Models\FreightService;
use App\Models\Sale;
use App\Models\SubLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FreightController extends Controller
{
    public function index(Request $request)
    {
        $services = FreightService::where('active', true)
            ->orderBy('name')
            ->get();

        $freights = Freight::with(['sale', 'service'])
            ->where('branch_id', auth()->user()->branch_id)
            ->when($request->freight_service_id, function ($q) use ($request) {
                $q->where(
                    'freight_service_id',
                    $request->freight_service_id
                );
            })
            ->latest()
            ->get();

        return view('freight_expenses.index', compact(
            'freights',
            'services'
        ));
    }

    public function create()
    {
        $sales = Sale::where('branch_id', auth()->user()->branch_id)
            ->where('currency', 'USD')
            ->whereDoesntHave('freights', function ($q) {
                $q->where('status', 'active');
            })
            ->latest()
            ->get();

        $services = FreightService::where('active', true)
            ->orderBy('name')
            ->get();

        return view('freight_expenses.create', compact(
            'sales',
            'services'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_type' => 'required|in:invoice,opening',
            'sale_id' => 'required_if:invoice_type,invoice|nullable|exists:sales,id',
            'invoice_id' => 'required_if:invoice_type,opening|nullable|string|max:255',
            'freight_service_id' => 'required|exists:freight_services,id',
            'date' => 'required|date',
            'exchange_rate' => 'required|numeric|gt:0',
            'amount_usd' => 'required|numeric|gt:0',
        ]);

        $branchId = auth()->user()->branch_id;

        DB::transaction(function () use ($request, $branchId) {

            $saleId = null;
            $invoiceId = null;
            $isOpeningBalance = 0;

            if ($request->invoice_type === 'invoice') {

                $sale = Sale::where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->findOrFail($request->sale_id);

                $saleId = $sale->id;

                // Use the invoice number from the existing sales table
                $invoiceId = $sale->invoice_id;

                $isOpeningBalance = 0;
            } else {

                $saleId = null;

                // Old-system invoice number entered manually
                $invoiceId = trim($request->invoice_id);

                $isOpeningBalance = 1;
            }

            $amountLkr = $request->amount_usd * $request->exchange_rate;

            $freight = Freight::create([
                'branch_id' => $branchId,
                'sale_id' => $saleId,
                'is_opening_balance' => $isOpeningBalance,
                'invoice_id' => $invoiceId,
                'freight_service_id' => $request->freight_service_id,
                'date' => $request->date,
                'exchange_rate' => $request->exchange_rate,
                'amount_usd' => $request->amount_usd,
                'amount_lkr' => $amountLkr,
                'total_paid' => 0,
                'balance_amount' => $amountLkr,
                'status' => 'active',
            ]);

            if ($request->invoice_type === 'invoice') {
                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->date,

                    'description' => 'Freight Expense - Freight #' .
                        $freight->id . ' - Invoice #' . $invoiceId,

                    'entries' => [
                        [
                            'ledger_id' => 15,
                            'sub_ledger_id' => null,
                            'debit' => $amountLkr,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 18,
                            'sub_ledger_id' => $freight->service->liability_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amountLkr,
                        ],
                    ]
                ]);
            } else {
                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->date,
                    'created_by' => auth()->id(),
                    'description' => 'Freight Opening Balance - '
                        . $freight->service->name
                        . ' - Freight ID: '
                        . $freight->id,
                    'entries' => [
                        [
                            'ledger_id' => 15,
                            'sub_ledger_id' => null,
                            'debit' => $amountLkr,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 18,
                            'sub_ledger_id' => $freight->liability_sub_ledger_id,  // liabilty sub ledger
                            'debit' => 0,
                            'credit' => $amountLkr,
                        ],
                    ],
                ]);
            }
        });

        return redirect()
            ->route('freights.index')
            ->with('success', 'Freight created successfully.');
    }

    public function show($id)
    {
        $freight = Freight::with([
            'sale',
            'service',
            'payments'
        ])
            ->where('branch_id', auth()->user()->branch_id)
            ->findOrFail($id);
        $paymentSubLedgers = SubLedger::where('ledger_id', 1)->get();



        return view('freight_expenses.show', compact('freight', 'paymentSubLedgers'));
    }

    public function payment(Request $request, $id)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'note' => 'nullable|string',
            'sub_ledger_id' => 'required|exists:sub_ledgers,id'
        ]);

        DB::transaction(function () use ($request, $id) {

            $freight = Freight::lockForUpdate()
                ->where('branch_id', auth()->user()->branch_id)
                ->findOrFail($id);

            if ($freight->status !== 'active') {
                throw ValidationException::withMessages([
                    'amount' => 'Cancelled freight cannot be paid.'
                ]);
            }

            if ($request->amount > $freight->balance_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the balance.'
                ]);
            }

            FreightPayment::create([
                'freight_id' => $freight->id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'note' => $request->note,
            ]);

            $freight->update([
                'total_paid' =>
                $freight->total_paid + $request->amount,

                'balance_amount' =>
                $freight->balance_amount - $request->amount,
            ]);

            Accounting::postJournal([
                'branch_id' => auth()->user()->branch_id,
                'date' => $request->payment_date,
                'description' => 'Freight Payment: ' . $freight->service->name . ' for Sale ID: ' . $freight->sale_id,

                'entries' => [
                    [
                        'ledger_id' => 1,
                        'sub_ledger_id' => $request->sub_ledger_id,
                        'debit' => 0,
                        'credit' => $request->amount,
                    ],
                    [
                        'ledger_id' => 5,
                        'sub_ledger_id' =>  $freight->service->liability_sub_ledger_id,
                        'debit' => $request->amount,
                        'credit' => 0,
                    ],
                ]
            ]);
        });

        return back()->with(
            'success',
            'Payment added successfully.'
        );
    }

    public function cancel($id)
    {
        DB::transaction(function () use ($id) {

            $freight = Freight::lockForUpdate()
                ->with('sale')
                ->where('branch_id', auth()->user()->branch_id)
                ->findOrFail($id);

            if ($freight->total_paid > 0) {
                throw ValidationException::withMessages([
                    'freight' =>
                    'Cannot cancel freight with payments.'
                ]);
            }

            $freight->update([
                'status' => 'cancelled',
            ]);

            Accounting::postJournal([
                'branch_id' => auth()->user()->branch_id,
                'date' => $freight->date,
                'description' => 'Cancelled Freight Expense - Freight #' . $freight->id . ' - Invoice #' . $freight->sale->invoice_id,

                'entries' => [
                    [
                        'ledger_id' => 10,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $freight->amount_lkr,
                    ],
                    [
                        'ledger_id' => 5,
                        'sub_ledger_id' =>  $freight->service->liability_sub_ledger_id,
                        'debit' =>  $freight->amount_lkr,
                        'credit' => 0,
                    ],
                ]
            ]);
        });

        return back()->with(
            'success',
            'Freight cancelled successfully.'
        );
    }

    public function statement(Request $request, $id)
    {
        $freight = Freight::with([
            'sale',
            'service',
            'openingJournalEntry'
        ])
            ->where('branch_id', auth()->user()->branch_id)
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

        $fromDate = Carbon::parse($fromDate)->toDateString();
        $toDate = Carbon::parse($toDate)->toDateString();

        /*
    |--------------------------------------------------------------------------
    | Build complete statement
    |--------------------------------------------------------------------------
    */

        $completeLedger = collect();

        /*
    |--------------------------------------------------------------------------
    | Opening Balance Freight
    |--------------------------------------------------------------------------
    | If is_opening_balance = 1, the freight itself represents the
    | opening balance from the old system.
    */

        if ($freight->is_opening_balance) {

            $completeLedger->push([
                'date' => Carbon::parse($freight->date)->toDateString(),
                'module' => 'Freight',
                'reference' => 'Opening Invoice #' . $freight->invoice_id,
                'type' => 'Opening Balance',
                'description' => 'Freight Opening Balance - Invoice #'
                    . $freight->invoice_id,
                'debit' => (float) $freight->amount_lkr,
                'credit' => 0,
            ]);
        } else {

            /*
        |--------------------------------------------------------------------------
        | Normal Sales Invoice Freight
        |--------------------------------------------------------------------------
        | Connected through sale_id.
        */

            $invoiceNumber = $freight->sale->invoice_number
                ?? $freight->invoice_id
                ?? $freight->sale_id;

            $completeLedger->push([
                'date' => Carbon::parse($freight->date)->toDateString(),
                'module' => 'Freight',
                'reference' => 'Freight #' . $freight->id,
                'type' => 'Invoice',
                'description' => 'Freight Invoice - '
                    . ($freight->service->name ?? 'Freight Service')
                    . ' (Invoice: ' . $invoiceNumber . ')',
                'debit' => (float) $freight->amount_lkr,
                'credit' => 0,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

        $payments = FreightPayment::where('freight_id', $freight->id)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {

            $completeLedger->push([
                'date' => Carbon::parse($payment->payment_date)->toDateString(),
                'module' => 'Freight',
                'reference' => 'Payment #' . $payment->id,
                'type' => 'Payment',
                'description' => $payment->note ?: 'Freight Payment',
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Sort and calculate running balance
    |--------------------------------------------------------------------------
    */

        $completeLedger = $completeLedger
            ->sortBy('date')
            ->values();

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
    | Period totals
    |--------------------------------------------------------------------------
    */

        $periodPurchase = (float) $ledger->sum('debit');

        $periodPaid = (float) $ledger->sum('credit');

        $balance = $all
            ? (float) ($ledger->last()['balance'] ?? 0)
            : $openingBalance + $periodPurchase - $periodPaid;

        /*
    |--------------------------------------------------------------------------
    | Recalculate displayed balances
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
    | Cash / Bank subledgers
    |--------------------------------------------------------------------------
    */

        $paymentSubLedgers = \App\Models\SubLedger::where('ledger_id', 1)
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view('freight_expenses.statement', compact(
            'freight',
            'ledger',
            'all',
            'fromDate',
            'toDate',
            'openingBalance',
            'periodPurchase',
            'periodPaid',
            'balance',
            'paymentSubLedgers'
        ));
    }

    public function storeOpeningBalance(Request $request, $id)
    {
        $request->validate([
            'opening_date' => ['required', 'date'],
            'opening_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($request, $id) {

            $branchId = auth()->user()->branch_id;

            $freight = Freight::with('service')
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($freight->opening_journal_entry_id) {
                throw ValidationException::withMessages([
                    'opening_amount' => 'An opening balance journal already exists for this freight.',
                ]);
            }

            $service = $freight->service;

            if (!$service || !$service->liability_sub_ledger_id) {
                throw ValidationException::withMessages([
                    'opening_amount' => 'The freight service liability sub-ledger is missing.',
                ]);
            }

            $freightService = FreightService::where(
                'id',
                $freight->freight_service_id
            )

                ->firstOrFail();

            $amount = (float) $request->opening_amount;

            $journal = Accounting::postJournal([
                'branch_id' => $branchId,
                'date' => $request->opening_date,
                'created_by' => auth()->id(),
                'description' => 'Freight Opening Balance - '
                    . $freightService->name
                    . ' - Freight ID: '
                    . $freight->id,
                'entries' => [
                    [
                        'ledger_id' => 17,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 5,
                        'sub_ledger_id' => $freightService->liability_sub_ledger_id,  // liabilty sub ledger
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);

            // Store the journal reference only; no opening amount column.
            $freight->update([
                'opening_journal_entry_id' => $journal->id,
                'balance_amount' => (float) $freight->balance_amount + $amount,
            ]);
        });

        return redirect()
            ->route('freight-expenses.statement', $id)
            ->with('success', 'Opening balance recorded successfully.');
    }

    public function statementService(Request $request, $id)
    {
        $branchId = auth()->user()->branch_id;

        $service = FreightService::findOrFail($id);

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

        $fromDate = Carbon::parse($fromDate)->toDateString();
        $toDate = Carbon::parse($toDate)->toDateString();

        /*
    |--------------------------------------------------------------------------
    | Get all freight records for this service
    |--------------------------------------------------------------------------
    */

        $freights = Freight::with('sale')
            ->where('branch_id', $branchId)
            ->where('freight_service_id', $service->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $freightIds = $freights->pluck('id');

        /*
    |--------------------------------------------------------------------------
    | Build complete statement
    |--------------------------------------------------------------------------
    */

        $completeLedger = collect();

        // Freight invoices and opening balances
        foreach ($freights as $freight) {

            $isOpening = (bool) $freight->is_opening_balance;

            $invoiceId = $freight->invoice_id
                ?? ($freight->sale->invoice_number ?? '-');

            $completeLedger->push([
                'date' => Carbon::parse($freight->date)->toDateString(),

                'module' => 'Freight',

                'reference' => 'Freight #' . $freight->id
                    . ' / Invoice #' . $invoiceId,

                'type' => $isOpening ? 'Opening Balance' : 'Invoice',

                'description' => $isOpening
                    ? 'Opening Balance - Invoice #' . $invoiceId
                    : 'Freight Invoice - Invoice #' . $invoiceId,

                'debit' => (float) $freight->amount_lkr,

                'credit' => 0,

                'sequence' => 1,

                'id' => $freight->id,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Freight payments
    |--------------------------------------------------------------------------
    */

        $payments = FreightPayment::whereIn('freight_id', $freightIds)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {

            $completeLedger->push([
                'date' => Carbon::parse($payment->payment_date)->toDateString(),

                'module' => 'Freight',

                'reference' => 'Payment #' . $payment->id
                    . ' / Freight #' . $payment->freight_id,

                'type' => 'Payment',

                'description' => $payment->note ?: 'Freight Payment',

                'debit' => 0,

                'credit' => (float) $payment->amount,

                'sequence' => 2,

                'id' => $payment->id,
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

        $periodPurchase = (float) $ledger->sum('debit');

        $periodPaid = (float) $ledger->sum('credit');

        $balance = $all
            ? (float) ($ledger->last()['balance'] ?? 0)
            : $openingBalance + $periodPurchase - $periodPaid;

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

        return view('freight_expenses.statement_service', compact(
            'service',
            'freights',
            'ledger',
            'all',
            'fromDate',
            'toDate',
            'openingBalance',
            'periodPurchase',
            'periodPaid',
            'balance',
            'paymentSubLedgers'
        ));
    }
}
