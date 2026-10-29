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
            'sale_id' => 'required|exists:sales,id',
            'freight_service_id' =>
            'required|exists:freight_services,id',
            'date' => 'required|date',
            'exchange_rate' => 'required|numeric|gt:0',
            'amount_usd' => 'required|numeric|gt:0',
        ]);

        $sale = Sale::where('branch_id', auth()->user()->branch_id)->findOrFail($request->sale_id);

        $amountLkr = $request->amount_usd * $request->exchange_rate;

        $freight = Freight::create([
            'branch_id' => auth()->user()->branch_id,
            'sale_id' => $sale->id,
            'freight_service_id' => $request->freight_service_id,
            'date' => $request->date,
            'exchange_rate' => $request->exchange_rate,
            'amount_usd' => $request->amount_usd,
            'amount_lkr' => $amountLkr,
            'total_paid' => 0,
            'balance_amount' => $amountLkr,
            'status' => 'active',
        ]);

        Accounting::postJournal([
            'branch_id' => auth()->user()->branch_id,
            'date' => $request->date,
            'description' => 'Freight Expense - Freight #' . $freight->id . ' - Invoice #' . $sale->invoice_number,

            'entries' => [
                [
                    'ledger_id' => 10,
                    'sub_ledger_id' => null,
                    'debit' => $amountLkr,
                    'credit' => 0,
                ],
                [
                    'ledger_id' => 5,
                    'sub_ledger_id' =>  $freight->service->liability_sub_ledger_id,
                    'debit' => 0,
                    'credit' => $amountLkr,
                ],
            ]
        ]);

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
        $freight = Freight::with(['sale', 'service', 'openingJournalEntry'])
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
    | Opening balance journal
    |--------------------------------------------------------------------------
    | The journal credits ledger 5 / the freight service liability subledger.
    | In this supplier-style statement, that amount increases the payable
    | balance, so it is displayed as a debit in the statement.
    */

        $openingAmount = 0;
        $openingDate = null;

        if (
            $freight->opening_journal_entry_id &&
            $freight->service &&
            $freight->service->liability_sub_ledger_id
        ) {
            $openingQuery = DB::table('journal_entry_details')
                ->where(
                    'journal_entry_id',
                    $freight->opening_journal_entry_id
                )
                ->where('ledger_id', 5)
                ->where(
                    'sub_ledger_id',
                    $freight->service->liability_sub_ledger_id
                );

            $openingAmount = (float) (clone $openingQuery)->sum('credit');

            $openingDate = DB::table('journal_entries')
                ->where('id', $freight->opening_journal_entry_id)
                ->value('journal_date');
        }

        /*
    |--------------------------------------------------------------------------
    | Build the complete statement
    |--------------------------------------------------------------------------
    */

        $completeLedger = collect();

        // Opening balance journal
        if ($openingAmount > 0 && $openingDate) {
            $completeLedger->push([
                'date' => Carbon::parse($openingDate)->toDateString(),
                'module' => 'Freight',
                'reference' => 'Journal #' . $freight->opening_journal_entry_id,
                'type' => 'Opening Balance',
                'description' => 'Freight Opening Balance',
                'debit' => $openingAmount,
                'credit' => 0,
            ]);
        }

        // Freight invoice
        $invoiceDate = $freight->created_at
            ? $freight->created_at->toDateString()
            : now()->toDateString();

        $invoiceAmount = (float) $freight->amount_lkr;

        $completeLedger->push([
            'date' => $invoiceDate,
            'module' => 'Freight',
            'reference' => 'Freight #' . $freight->id,
            'type' => 'Invoice',
            'description' => 'Freight Invoice - '
                . ($freight->service->name ?? 'Freight Service')
                . ' (Sale ID: ' . $freight->sale_id . ')',
            'debit' => $invoiceAmount,
            'credit' => 0,
        ]);

        // Payments made against this freight
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

        // Sort by date, then calculate balances over the complete history.
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
    | Apply date filtering after calculating the complete running balance
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

        // Period totals exclude the brought-forward balance.
        $periodPurchase = (float) $ledger->sum('debit');
        $periodPaid = (float) $ledger->sum('credit');

        // The final balance must include the brought-forward amount.
        $balance = $all
            ? (float) ($ledger->last()['balance'] ?? 0)
            : $openingBalance + $periodPurchase - $periodPaid;

        /*
     * Recalculate displayed balances from the brought-forward amount so the
     * first row in a filtered period continues from the opening balance.
     */
        if (!$all) {
            $runningBalance = $openingBalance;

            $ledger = $ledger->map(function ($row) use (&$runningBalance) {
                $runningBalance += $row['debit'] - $row['credit'];
                $row['balance'] = $runningBalance;

                return $row;
            });
        }

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

            $openingLedgerId = (int) config(
                'accounting.opening_balance_ledger_id'
            );

            if (!$openingLedgerId) {
                throw ValidationException::withMessages([
                    'opening_amount' => 'Configure the opening balance ledger ID first.',
                ]);
            }

            $liabilitySubLedger = SubLedger::where(
                'id',
                $service->liability_sub_ledger_id
            )
                ->where('ledger_id', 5)
                ->where('branch_id', $branchId)
                ->firstOrFail();

            $amount = (float) $request->opening_amount;

            $journal = Accounting::postJournal([
                'branch_id' => $branchId,
                'date' => $request->opening_date,
                'created_by' => auth()->id(),
                'description' => 'Freight Opening Balance - '
                    . $service->name
                    . ' - Freight ID: '
                    . $freight->id,
                'entries' => [
                    [
                        'ledger_id' => $openingLedgerId,
                        'sub_ledger_id' => null,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 5,
                        'sub_ledger_id' => $liabilitySubLedger->id,
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
}
