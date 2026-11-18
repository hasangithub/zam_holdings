<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Customer;
use App\Models\CustomerCurrencyOpeningBalance;
use App\Models\CustomerCurrencyPayment;
use App\Models\JournalEntryReference;
use App\Models\Sale;
use App\Models\SalesAmendment;
use App\Models\SalesPayment;
use App\Models\SubLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    // Local customers
    public function local()
    {
        $customers = Customer::where('branch_id', auth()->user()->branch_id)
            ->where('customer_type', 'local')
            ->latest()
            ->get();

        return view('customers.local', compact('customers'));
    }

    // Export customers
    public function export()
    {
        $customers = Customer::where('branch_id', auth()->user()->branch_id)
            ->where('customer_type', 'export')
            ->latest()
            ->get();

        return view('customers.export', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);


        DB::transaction(function () use ($request) {

            $request->merge(['branch_id' => auth()->user()->branch_id]);
            $customer = Customer::create($request->all());


            /*
        |--------------------------------------------------------------------------
        | Create Liability Sub Ledger
        |--------------------------------------------------------------------------
        */

            $subLedger = SubLedger::create([
                'ledger_id' => 6,
                'name'      => $customer->name,
            ]);

            $customer->update([
                'receivable_sub_ledger_id' => $subLedger->id,
            ]);
        });

        if ($request->customer_type == 'export') {
            return redirect()->route('customers.export')->with('success', 'Export Customer created successfully.');
        }
        return redirect()->route('customers.local')->with('success', 'Local Customer created successfully.');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        if ((int) $customer->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to edit this customer.');
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        if ((int) $customer->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to update this customer.');
        }

        $customer->update($request->all());

        return redirect()->route('customers.index');
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();
        return back();
    }

    public function statement(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        if ((int) $customer->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to edit this customer.');
        }

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
| CUSTOMER OPENING BALANCE JOURNAL
|--------------------------------------------------------------------------
*/

        if ($customer->opening_journal_entry_id) {

            $openingJournal = DB::table('journal_entries')
                ->where('id', $customer->opening_journal_entry_id)
                ->first();

            if ($openingJournal) {

                $openingAmount = (float) DB::table('journal_entry_details')
                    ->where('journal_entry_id', $customer->opening_journal_entry_id)
                    ->where('sub_ledger_id', $customer->receivable_sub_ledger_id)
                    ->sum('debit');

                if ($openingAmount > 0) {

                    $ledger->push([
                        'date' => $openingJournal->journal_date,
                        'module' => 'Opening Balance',
                        'type' => 'Opening Balance',
                        'reference' => 'OPN-' . $openingJournal->id,
                        'description' => $openingJournal->description
                            ?? 'Customer Opening Balance',
                        'debit' => $openingAmount,
                        'credit' => 0,
                    ]);
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

        $sales = Sale::where('customer_id', $id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $payments = SalesPayment::where('customer_id', $id)->get();

        foreach ($sales as $sale) {

            $ledger->push([
                'date' => $sale->sale_date,
                'module' => 'Sale',
                'type' => 'Invoice',
                'debit' => $sale->total,
                'credit' => 0,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SALES PAYMENTS
    |--------------------------------------------------------------------------
    */

        foreach ($payments as $payment) {

            $ledger->push([
                'date' => $payment->payment_date,
                'module' => 'Sale',
                'type' => 'Payment',
                'payment_id' => $payment->id,
                'debit' => 0,
                'credit' => $payment->amount,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SALES AMENDMENTS
    |--------------------------------------------------------------------------
    */

        $amendments = SalesAmendment::where('customer_id', $id)
            ->where('status', 'approved')
            ->get();

        foreach ($amendments as $amendment) {

            $amount = (float) $amendment->amount;

            $ledger->push([
                'date' => $amendment->amendment_date,
                'module' => 'Sale',
                'type' => 'Amendment',
                'amendment_id' => $amendment->id,
                'reference' => 'AMD-' . $amendment->id,
                'description' => $amendment->reason,
                'debit' => $amount > 0 ? $amount : 0,
                'credit' => $amount < 0 ? abs($amount) : 0,
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

                // Before From Date = Opening Balance
                if ($row['date'] < $fromDate) {

                    $openingBalance +=
                        $row['debit'] - $row['credit'];
                }

                // From Date to To Date
                elseif (
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

            $running +=
                $row['debit'] - $row['credit'];

            $row['balance'] = $running;

            return $row;
        });


        /*
    |--------------------------------------------------------------------------
    | TOTALS
    |--------------------------------------------------------------------------
    */

        $periodSales = $ledger->sum('debit');

        $periodPaid = $ledger->sum('credit');

        $balance = $openingBalance +
            $periodSales -
            $periodPaid;


        /*
    |--------------------------------------------------------------------------
    | ALL = FINAL CUSTOMER BALANCE
    |--------------------------------------------------------------------------
    */

        if ($all) {

            $balance = $ledger->last()['balance'] ?? 0;
        }

        $paymentSubLedgers = SubLedger::where('ledger_id', 1)->get();

        $currencyOptions = [
            'USD' => 'US Dollar',
            'SAR' => 'Saudi Riyal',
            'AED' => 'UAE Dirham',
            'EUR' => 'Euro',
        ];


        return view('customers.statement', compact(
            'customer',
            'ledger',
            'balance',
            'openingBalance',
            'periodSales',
            'periodPaid',
            'fromDate',
            'toDate',
            'all',
            'paymentSubLedgers',
            'currencyOptions'
        ));
    }

    public function storePayment(Request $request, int $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
            'sub_ledger_id' => 'required|exists:sub_ledgers,id',
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $customer = Customer::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                if (!$customer->receivable_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This customer does not have a liability sub-ledger.']);
                }

                $payment = SalesPayment::create([

                    'customer_id' => $customer->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->method,
                    'note' => $request->note,
                ]);

                $paymentLedgerId = 1;
                $paymentSubLedgerId = $request->sub_ledger_id;

                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for sale - ' . $customer->name,

                    'entries' => [
                        [
                            'ledger_id' => $paymentLedgerId,
                            'sub_ledger_id' => $paymentSubLedgerId,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 6,
                            'sub_ledger_id' =>  $customer->receivable_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amount,
                        ],
                    ]
                ]);

                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type'       => SalesPayment::class,
                    'model_id'         => $payment->id,
                    'action'           => 'created',
                ]);
            });


            return back()->with('success', 'Payment added successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Unable to process the payment.');
        }
    }

    public function storeOpeningBalance(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date'   => 'required|date',
        ]);

        if ($customer->opening_journal_entry_id) {
            return back()->with(
                'error',
                'Opening balance has already been entered for this customer.'
            );
        }

        DB::transaction(function () use ($request, $customer) {

            $amount = $request->amount;

            $journal = Accounting::postJournal([
                'branch_id' => $customer->branch_id,
                'date' => $request->date,
                'description' => 'Opening balance - Customer: '
                    . $customer->name,

                'entries' => [
                    [
                        'ledger_id' => 17,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                    [
                        'ledger_id' => 6,
                        'sub_ledger_id' =>  $customer->receivable_sub_ledger_id,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                ]
            ]);

            // Store journal header ID in customer
            $customer->update([
                'opening_journal_entry_id' => $journal->id,
            ]);
        });

        return back()->with(
            'success',
            'Customer opening balance saved successfully.'
        );
    }

    public function updatePayment(Request $request, int $customerId, int $paymentId)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
        ]);

        try {

            DB::transaction(function () use ($request, $customerId, $paymentId) {

                $branchId = auth()->user()->branch_id;

                // Lock and validate customer.
                $customer = Customer::where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->findOrFail($customerId);

                if (!$customer->receivable_sub_ledger_id) {
                    throw ValidationException::withMessages([
                        'amount' => 'This customer does not have a receivable sub-ledger.'
                    ]);
                }

                // Lock the existing payment.
                $payment = SalesPayment::where('customer_id', $customer->id)
                    ->lockForUpdate()
                    ->findOrFail($paymentId);

                // Find the active journal reference for this payment.
                $reference = JournalEntryReference::where(
                    'model_type',
                    SalesPayment::class
                )
                    ->where('model_id', $payment->id)
                    ->whereIn('action', ['created', 'updated'])
                    ->latest('id')
                    ->first();

                if (!$reference) {
                    throw ValidationException::withMessages([
                        'amount' => 'The original payment journal could not be found.'
                    ]);
                }

                // Preserve the original cashbook subledger.
                $cashSubLedgerId = DB::table('journal_entry_details')
                    ->where('journal_entry_id', $reference->journal_entry_id)
                    ->where('ledger_id', 1)
                    ->where('debit', '>', 0)
                    ->value('sub_ledger_id');

                if (!$cashSubLedgerId) {
                    throw ValidationException::withMessages([
                        'amount' => 'The original cashbook subledger could not be identified.'
                    ]);
                }

                // Reverse the previous payment journal.
                Accounting::reverse(
                    SalesPayment::class,
                    $payment->id
                );

                $amount = (float) $request->amount;

                // Update the existing payment record.
                $payment->update([
                    'amount' => $amount,
                    'payment_date' => $request->payment_date,
                ]);

                // Post replacement journal.
                $journal = Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for sale - ' . $customer->name,

                    'entries' => [
                        [
                            'ledger_id' => 1,
                            'sub_ledger_id' => $cashSubLedgerId,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => 6,
                            'sub_ledger_id' => $customer->receivable_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amount,
                        ],
                    ]
                ]);

                // Link the replacement journal to the existing payment.
                JournalEntryReference::create([
                    'journal_entry_id' => $journal->id,
                    'model_type' => SalesPayment::class,
                    'model_id' => $payment->id,
                    'action' => 'updated',
                ]);
            });

            return back()->with('success', 'Payment updated successfully.');
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', 'Unable to update the payment.');
        }
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

        $customer = Customer::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

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

        $openingRecord = CustomerCurrencyOpeningBalance::where('customer_id', $customer->id)
            ->where('branch_id', $customer->branch_id)
            ->where('currency', $currency)
            ->first();

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

        $sales = Sale::where('customer_id', $customer->id)
            // ->where('branch_id', $customer->branch_id)
            ->where('status', '!=', 'cancelled')
            ->where('currency', $currency)
            ->get();

        foreach ($sales as $sale) {

            $ledger->push([
                'date' => $sale->sale_date,
                'module' => 'Sale',
                'type' => 'Invoice',
                'reference' => 'INV-' . $sale->id,
                'description' => 'Sales Invoice',
                'debit' => (float) $sale->total_foreign,
                'credit' => 0,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | CURRENCY PAYMENTS
    |--------------------------------------------------------------------------
    */

        $payments = CustomerCurrencyPayment::where('customer_id', $customer->id)
            ->where('branch_id', $customer->branch_id)
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

        $currencyPayments = CustomerCurrencyPayment::where('customer_id', $customer->id)
            ->where('branch_id', $customer->branch_id)
            ->where('currency', $currency)
            ->latest('payment_date')
            ->get();

        return view('customers.currency-statement', compact(
            'customer',
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

    public function storeCurrencyOpeningBalance(Request $request, $id, $currency)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];

        abort_unless(in_array($currency, $currencyOptions), 404);

        $request->validate([
            'opening_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $customer = Customer::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        CustomerCurrencyOpeningBalance::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'currency' => $currency,
            ],
            [
                'branch_id' => $customer->branch_id,
                'opening_date' => $request->opening_date,
                'amount' => $request->amount,
                'note' => $request->note,
            ]
        );

        return redirect()
            ->route('customers.currency-statement', [
                $customer->id,
                $currency,
            ])
            ->with('success', 'Currency opening balance saved successfully.');
    }

    public function storeCurrencyPayment(Request $request, $id, $currency)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];

        abort_unless(in_array($currency, $currencyOptions), 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $customer = Customer::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        CustomerCurrencyPayment::create([
            'customer_id' => $customer->id,
            'branch_id' => $customer->branch_id,
            'currency' => $currency,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('customers.currency-statement', [
                $customer->id,
                $currency,
            ])
            ->with('success', 'Currency payment saved successfully.');
    }

    public function updateCurrencyPayment(Request $request, $id, $currency, $paymentId)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];

        abort_unless(in_array($currency, $currencyOptions), 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $customer = Customer::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        $payment = CustomerCurrencyPayment::where('id', $paymentId)
            ->where('customer_id', $customer->id)
            ->where('branch_id', $customer->branch_id)
            ->where('currency', $currency)
            ->firstOrFail();

        $payment->update([
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'note' => $request->note,
        ]);

        return redirect()
            ->route('customers.currency-statement', [
                $customer->id,
                $currency,
            ])
            ->with('success', 'Currency payment updated successfully.');
    }

    public function deleteCurrencyPayment($id, $currency, $paymentId)
    {
        $currencyOptions = ['USD', 'SAR', 'AED', 'EUR'];

        abort_unless(in_array($currency, $currencyOptions), 404);

        $customer = Customer::where('id', $id)
            ->where('branch_id', auth()->user()->branch_id)
            ->firstOrFail();

        $payment = CustomerCurrencyPayment::where('id', $paymentId)
            ->where('customer_id', $customer->id)
            ->where('branch_id', $customer->branch_id)
            ->where('currency', $currency)
            ->firstOrFail();

        $payment->delete();

        return redirect()
            ->route('customers.currency-statement', [
                $customer->id,
                $currency,
            ])
            ->with('success', 'Currency payment deleted successfully.');
    }
}
