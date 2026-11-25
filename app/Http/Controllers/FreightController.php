<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Freight;
use App\Models\FreightCurrencyPayment;
use App\Models\FreightPayment;
use App\Models\FreightService;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\JournalEntryReference;
use App\Models\Sale;
use App\Models\SubLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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

        $cashbooks = SubLedger::where('ledger_id', 1)
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view('freight_expenses.index', compact(
            'freights',
            'services',
            'cashbooks'
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
                $journal = Accounting::postJournal([
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

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type'       => Freight::class,
                    'model_id'         => $freight->id,
                    'action'           => 'created',
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
                            'ledger_id' => 17,
                            'sub_ledger_id' => null,
                            'debit' => $amountLkr,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 15,
                            'sub_ledger_id' => null,  // liabilty sub ledger
                            'debit' => 0,
                            'credit' => $amountLkr,
                        ],
                    ],
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type'       => Freight::class,
                    'model_id'         => $freight->id,
                    'action'           => 'created',
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
                ->findOrFail($request->freight_id);

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

            $freightPayment = FreightPayment::create([
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

            $journal = Accounting::postJournal([
                'branch_id' => auth()->user()->branch_id,
                'date' => $request->payment_date,
                'description' => 'Freight Payment: ' . $freight->service->name . ' for Freight ID: ' . $freight->id,

                'entries' => [
                    [
                        'ledger_id' => 1,
                        'sub_ledger_id' => $request->sub_ledger_id,
                        'debit' => 0,
                        'credit' => $request->amount,
                    ],
                    [
                        'ledger_id' => 18,
                        'sub_ledger_id' =>  $freight->service->liability_sub_ledger_id,
                        'debit' => $request->amount,
                        'credit' => 0,
                    ],
                ]
            ]);

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type'       => FreightPayment::class,
                'model_id'         => $freightPayment->id,
                'action'           => 'created',
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
                'description' => 'Cancelled Freight Expense - Freight #' . $freight->id . ' - Invoice #' . $freight->invoice_id,

                'entries' => [
                    [
                        'ledger_id' => 15,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $freight->amount_lkr,
                    ],
                    [
                        'ledger_id' => 18,
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
                ->findOrFail($request->freight_id);

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
                        'ledger_id' => 15,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 18,
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
            ->route('freight-expenses.statement_service', $id)
            ->with('success', 'Opening balance recorded successfully.');
    }

    public function statementService(Request $request, $id)
    {
        $branchId = auth()->user()->branch_id;

        $service = FreightService::findOrFail($id);

        $currencyOptions = [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'AED' => 'UAE Dirham',
            'EUR' => 'Euro',
        ];

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
            'paymentSubLedgers',
            'currencyOptions'
        ));
    }

    public function updateServicePayment(Request $request, $serviceId, $paymentId)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
        ]);

        $branchId = auth()->user()->branch_id;

        try {
            DB::transaction(function () use (
                $request,
                $serviceId,
                $paymentId,
                $branchId
            ) {
                // Confirm the freight service exists.
                $service = FreightService::findOrFail($serviceId);

                // Find a freight record belonging to this branch and service.
                $freight = Freight::where('branch_id', $branchId)
                    ->where('freight_service_id', $service->id)
                    ->whereHas('payments', function ($query) use ($paymentId) {
                        $query->where('id', $paymentId);
                    })
                    ->lockForUpdate()
                    ->firstOrFail();

                // Lock the existing payment.
                $payment = FreightPayment::where('freight_id', $freight->id)
                    ->where('id', $paymentId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Find the latest active journal reference for this payment.
                $reference = JournalEntryReference::where(
                    'model_type',
                    FreightPayment::class
                )
                    ->where('model_id', $payment->id)
                    ->whereIn('action', ['created', 'updated'])
                    ->latest('id')
                    ->first();

                if (!$reference) {
                    throw ValidationException::withMessages([
                        'amount' => 'No journal entry was found for this payment.',
                    ]);
                }

                // Load and verify the original journal.
                $oldJournal = JournalEntry::where('id', $reference->journal_entry_id)
                    ->where('branch_id', $branchId)
                    ->firstOrFail();

                $oldDetails = JournalEntryDetail::where(
                    'journal_entry_id',
                    $oldJournal->id
                )->get();

                // This implementation expects one debit and one credit entry.
                if (
                    $oldDetails->count() !== 2 ||
                    $oldDetails->where('debit', '>', 0)->count() !== 1 ||
                    $oldDetails->where('credit', '>', 0)->count() !== 1
                ) {
                    throw ValidationException::withMessages([
                        'amount' => 'The original payment journal does not have the expected debit and credit structure.',
                    ]);
                }

                // Preserve the original ledger and subledger accounts.
                $entries = [];

                foreach ($oldDetails as $detail) {
                    $entries[] = [
                        'ledger_id' => $detail->ledger_id,
                        'sub_ledger_id' => $detail->sub_ledger_id,
                        'debit' => (float) $detail->debit > 0
                            ? (float) $request->amount
                            : 0,
                        'credit' => (float) $detail->credit > 0
                            ? (float) $request->amount
                            : 0,
                    ];
                }

                // Reverse the previous journal.
                Accounting::reverse(FreightPayment::class, $payment->id);

                // Update the existing payment record.
                $payment->update([
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                ]);

                // Post the replacement journal.
                $newJournal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => $oldJournal->description,
                    'entries' => $entries,
                ]);

                // Link the replacement journal to the same payment.
                JournalEntryReference::create([
                    'journal_entry_id' => $newJournal->id,
                    'model_type' => FreightPayment::class,
                    'model_id' => $payment->id,
                    'action' => 'updated',
                ]);
            });

            return back()->with('success', 'Freight payment updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the freight payment.');
        }
    }



    public function bulkPayment(Request $request)
    {
        $request->validate([
            'freight_ids' => 'required|string',
            'payment_date' => 'required|date',
            'sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => 'nullable|string',
        ]);

        $freightIds = json_decode($request->freight_ids, true);

        if (
            !is_array($freightIds) ||
            empty($freightIds) ||
            count($freightIds) !== count(array_unique($freightIds))
        ) {
            throw ValidationException::withMessages([
                'freight_ids' => 'Invalid or duplicate freight selections.',
            ]);
        }

        $validator = Validator::make(
            ['freight_ids' => $freightIds],
            [
                'freight_ids' => 'required|array|min:1',
                'freight_ids.*' => 'required|integer|distinct',
            ]
        );

        $validator->validate();

        $branchId = auth()->user()->branch_id;

        // Validate selected cashbook
        $cashbook = SubLedger::where('id', $request->sub_ledger_id)
            ->where('ledger_id', 1)
            ->where('branch_id', $branchId)
            ->first();

        if (!$cashbook) {
            throw ValidationException::withMessages([
                'sub_ledger_id' => 'Invalid cashbook selected.',
            ]);
        }

        $result = DB::transaction(function () use (
            $request,
            $freightIds,
            $branchId
        ) {

            $paidCount = 0;
            $skippedCount = 0;
            $totalPayment = 0;

            foreach ($freightIds as $freightId) {

                $freight = Freight::with('service')
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->findOrFail($freightId);

                if ($freight->status !== 'active') {
                    throw ValidationException::withMessages([
                        'freight_ids' =>
                        "Freight invoice {$freight->invoice_id} is cancelled.",
                    ]);
                }

                if (!$freight->service) {
                    throw ValidationException::withMessages([
                        'freight_ids' =>
                        "Freight service missing for invoice {$freight->invoice_id}.",
                    ]);
                }

                // Existing payment calculation
                $invoiceAmount = (float) $freight->amount_lkr;
                $alreadyPaid = (float) $freight->total_paid;

                // Calculate actual outstanding amount
                $amount = round($invoiceAmount - $alreadyPaid, 2);

                // Already fully paid
                if ($amount <= 0) {
                    $skippedCount++;

                    continue;
                }

                // Create freight payment
                $freightPayment = FreightPayment::create([
                    'freight_id' => $freight->id,
                    'payment_date' => $request->payment_date,
                    'amount' => $amount,
                    'note' => $request->note ?? 'Full bulk freight payment',
                ]);

                // Update freight balances
                $freight->update([
                    'total_paid' => $alreadyPaid + $amount,
                    'balance_amount' => 0,
                ]);

                // Create journal entry
                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' =>
                    'Freight Full Payment: '
                        . $freight->service->name
                        . ' - Invoice: '
                        . $freight->invoice_id,

                    'entries' => [
                        [
                            'ledger_id' => 1,
                            'sub_ledger_id' => $request->sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amount,
                        ],
                        [
                            'ledger_id' => 18,
                            'sub_ledger_id' =>
                            $freight->service->liability_sub_ledger_id,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                    ],
                ]);

                // Link journal to freight payment
                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => FreightPayment::class,
                    'model_id' => $freightPayment->id,
                    'action' => 'created',
                ]);

                $paidCount++;
                $totalPayment += $amount;
            }

            return [
                'paid_count' => $paidCount,
                'skipped_count' => $skippedCount,
                'total_payment' => $totalPayment,
            ];
        });

        return back()->with(
            'success',
            "Bulk payment completed. "
                . "Paid invoices: {$result['paid_count']}. "
                . "Already paid/skipped: {$result['skipped_count']}. "
                . "Total payment: LKR "
                . number_format($result['total_payment'], 2)
        );
    }

    public function currencyStatement(Request $request, $id, $currency)
    {
        $currencyOptions = [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'AED' => 'UAE Dirham',
            'EUR' => 'Euro',
        ];

        abort_unless(array_key_exists($currency, $currencyOptions), 404);

        $branchId = auth()->user()->branch_id;

        $freightService = FreightService::where('id', $id)->firstOrFail();

        $freights = Freight::with('sale')
            ->where('branch_id', $branchId)
            ->where('freight_service_id', $freightService->id)
            ->get();

        $all = $request->boolean('all');

        $fromDate = $request->input(
            'from_date',
            now()->subDays(6)->format('Y-m-d')
        );

        $toDate = $request->input(
            'to_date',
            now()->format('Y-m-d')
        );

        $ledger = collect();
        $openingBalance = 0;

        /*
    |--------------------------------------------------------------------------
    | CURRENCY OPENING BALANCE
    |--------------------------------------------------------------------------
    */

        $openingRecord = [];

        if ($openingRecord) {

            $ledger->push([
                'date' => $openingRecord->opening_date,
                'module' => 'Opening Balance',
                'type' => 'Opening Balance',
                'reference' => 'OPN-' . $openingRecord->id,
                'description' => $openingRecord->note ?? 'Currency Opening Balance',
                'debit' => (float) $openingRecord->amount,
                'credit' => 0,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SALES INVOICES
    |--------------------------------------------------------------------------
    */

        $sales = Freight::where('freight_service_id', $freightService->id)
            // ->where('branch_id', $freight->branch_id)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($sales as $sale) {

            $ledger->push([
                'date' => $sale->date,
                'module' => 'Sale',
                'type' => 'Invoice',
                'reference' => 'INV-' . $sale->id,
                'description' => 'Freight Invoice',
                'debit' => (float) $sale->amount_usd,
                'credit' => 0,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | CURRENCY PAYMENTS
    |--------------------------------------------------------------------------
    */

        $payments = FreightCurrencyPayment::where('freight_id', $sale->id)
            ->where('branch_id', $sale->branch_id)
            ->where('currency', $currency)
            ->get();



        foreach ($payments as $payment) {

            $ledger->push([
                'date' => $payment->payment_date,
                'module' => 'Payment',
                'type' => 'Payment',
                'payment_id' => $payment->id,
                'reference' => $payment->reference ?: 'PAY-' . $payment->id,
                'description' => $payment->note ?? 'Currency Payment',
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SORT
    |--------------------------------------------------------------------------
    */

        $ledger = $ledger
            ->sortBy(function ($row) {
                return [
                    $row['date'],
                    $row['type'],
                    $row['reference'] ?? '',
                ];
            })
            ->values();

        /*
    |--------------------------------------------------------------------------
    | FILTER + OPENING BALANCE
    |--------------------------------------------------------------------------
    */

        if (!$all) {

            $filtered = collect();

            foreach ($ledger as $row) {

                if ($row['date'] < $fromDate) {

                    $openingBalance +=
                        $row['debit'] - $row['credit'];
                } elseif (
                    $row['date'] >= $fromDate &&
                    $row['date'] <= $toDate
                ) {

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

        $running = $openingBalance;

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

        $periodSales = $ledger
            ->where('type', 'Invoice')
            ->sum('debit');

        $periodPaid = $ledger
            ->where('type', 'Payment')
            ->sum('credit');

        $balance = $openingBalance + $periodSales - $periodPaid;

        if ($all) {
            $balance = $ledger->last()['balance'] ?? 0;
        }

        /*
    |--------------------------------------------------------------------------
    | PAYMENT AND OPENING HISTORY
    |--------------------------------------------------------------------------
    */

        $currencyPayments = FreightCurrencyPayment::where('freight_id', $sale->id)
            ->where('branch_id', $sale->branch_id)
            ->where('currency', $currency)
            ->latest('payment_date')
            ->get();

        return view('freight_expenses.currency-statement', compact(
            'freightService',
            'freights',
            'currency',
            'currencyOptions',
            'ledger',
            'openingBalance',
            'periodSales',
            'periodPaid',
            'balance',
            'fromDate',
            'toDate',
            'all',
            'openingRecord',
            'currencyPayments'
        ));
    }

    public function storeCurrencyPayment(Request $request, $id, $currency)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];
        $branchId = auth()->user()->branch_id;

        abort_unless(in_array($currency, $currencyOptions), 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $freightService = FreightService::where('id', $id)->firstOrFail();

        FreightCurrencyPayment::create([
            'freight_id' => $request->freight_id,
            'branch_id' => $branchId,
            'currency' => $currency,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('freight-expenses.currency-statement', [$freightService->id, $currency])
            ->with('success', 'Currency payment saved successfully.');
    }

    public function updateCurrencyPayment(Request $request, $id, $currency, $paymentId)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];
        $branchId = auth()->user()->branch_id;

        abort_unless(in_array($currency, $currencyOptions), 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $freightService = FreightService::where('id', $id)->firstOrFail();

        $payment = FreightCurrencyPayment::where('id', $paymentId)
            ->where('branch_id', $branchId)
            ->where('currency', $currency)
            ->firstOrFail();

        $payment->update([
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'note' => $request->note,
        ]);

        return redirect()
            ->route('freight-expenses.currency-statement', [
                $freightService->id,
                $currency,
            ])
            ->with('success', 'Currency payment updated successfully.');
    }

    public function deleteCurrencyPayment($id, $currency, $paymentId)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];

        abort_unless(in_array($currency, $currencyOptions), 404);

        $freightService = FreightService::where('id', $id)
            ->firstOrFail();

        $payment = FreightCurrencyPayment::where('id', $paymentId)
            ->where('freight_service_id', $freightService->id)
            ->where('currency', $currency)
            ->firstOrFail();

        $payment->delete();

        return redirect()
            ->route('customers.currency-statement', [
                $freightService->id,
                $currency,
            ])
            ->with('success', 'Currency payment deleted successfully.');
    }

    public function editData($id)
    {
        $freight = Freight::where('branch_id', auth()->user()->branch_id)
            ->where('is_opening_balance', 0)
            ->findOrFail($id);

        return response()->json([
            'date' => $freight->date,
            'exchange_rate' => $freight->exchange_rate,
            'amount_usd' => $freight->amount_usd,
        ]);
    }

    public function updateInvoice(Request $request, $serviceId, $freightId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'exchange_rate' => 'required|numeric|gt:0',
            'amount_usd' => 'required|numeric|gt:0',
        ]);

        $branchId = auth()->user()->branch_id;

        DB::transaction(function () use ($data, $branchId, $serviceId, $freightId) {

            $freight = Freight::where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($freightId);

            if ($freight->freight_service_id != $serviceId) {
                abort(403, 'Invalid freight service.');
            }

            if ($freight->is_opening_balance) {
                throw ValidationException::withMessages([
                    'amount_usd' => 'Opening balance records cannot be edited as invoice records.',
                ]);
            }

            $amountLkr = (float) $data['amount_usd']
                * (float) $data['exchange_rate'];

            // Do not allow invoice amount below payments already made
            if ($amountLkr < (float) $freight->total_paid) {
                throw ValidationException::withMessages([
                    'amount_usd' => 'Invoice amount cannot be less than the payments already made.',
                ]);
            }

            // Find the existing journal reference
            $reference = JournalEntryReference::where(
                'model_type',
                Freight::class
            )
                ->where('model_id', $freight->id)
                ->first();

            if (!$reference) {
                throw new \Exception(
                    'Freight invoice journal reference not found. Please link the existing journal first.'
                );
            }

            $journal = JournalEntry::where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($reference->journal_entry_id);

            $service = FreightService::where('id', $serviceId)
                ->firstOrFail();

            $liabilitySubLedgerId = $service->liability_sub_ledger_id;

            // Existing debit: Freight Expense (Ledger 15)
            $debitEntry = JournalEntryDetail::where('journal_entry_id', $journal->id)
                ->where('ledger_id', 15)
                ->where('debit', '>', 0)
                ->first();

            // Existing credit: Freight Service Liability (Ledger 18)
            $creditEntry = JournalEntryDetail::where('journal_entry_id', $journal->id)
                ->where('ledger_id', 18)
                ->first();

            if (!$debitEntry || !$creditEntry) {
                throw new \Exception(
                    'Freight invoice journal entries are incomplete.'
                );
            }

            // Update existing freight invoice
            $freight->update([
                'date' => $data['date'],
                'exchange_rate' => $data['exchange_rate'],
                'amount_usd' => $data['amount_usd'],
                'amount_lkr' => $amountLkr,
                'balance_amount' => $amountLkr - (float) $freight->total_paid,
            ]);

            // Update existing journal header
            $journal->update([
                'journal_date' => $data['date'],
                'description' => 'Freight Expense - Freight #'
                    . $freight->id
                    . ' - Invoice #'
                    . $freight->invoice_id,
            ]);

            // Update existing debit
            $debitEntry->update([
                'debit' => $amountLkr,
                'credit' => 0,
            ]);

            // Update existing liability credit
            $creditEntry->update([
                'sub_ledger_id' => $liabilitySubLedgerId,
                'debit' => 0,
                'credit' => $amountLkr,
            ]);
        });

        return redirect()
            ->route('freight-services.statement', $serviceId)
            ->with('success', 'Freight invoice updated successfully.');
    }
}
