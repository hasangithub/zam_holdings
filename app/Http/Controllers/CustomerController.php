<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalesPayment;
use App\Models\SubLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('customers.index', compact('customers'));
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

            /*
        |--------------------------------------------------------------------------
        | Create Supplier
        |--------------------------------------------------------------------------
        */

            $customer = Customer::create($request->all());


            /*
        |--------------------------------------------------------------------------
        | Create Liability Sub Ledger
        |--------------------------------------------------------------------------
        */

            $subLedger = SubLedger::create([

                'ledger_id' => 7,
                'name'      => $customer->name,
            ]);

            $customer->update([
                'receivable_sub_ledger_id' =>
                $subLedger->id,

            ]);
        });


        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Customer created successfully.'
            );
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        Customer::findOrFail($id)->update($request->all());
        return redirect()->route('customers.index');
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();
        return back();
    }

    public function statement(int $customerId)
    {
        $customer = Customer::findOrFail($customerId);

        $sales = Sale::where('customer_id', $customerId)
            ->selectRaw("
            id,
            invoice_id,
            sale_date as trans_date,
            total as debit,
            0 as credit,
            'Invoice' as type
        ");

        $payments = SalesPayment::where('customer_id', $customerId)
            ->selectRaw("
            id,
            NULL as invoice_id,
            payment_date as trans_date,
            0 as debit,
            amount as credit,
            'Payment' as type
        ");

        $transactions = $sales
            ->unionAll($payments)
            ->orderBy('trans_date')
            ->get();

        $totalSales = Sale::where('customer_id', $customerId)->sum('total');

        $totalPayments = SalesPayment::where('customer_id', $customerId)
            ->sum('amount');

        $outstanding = $totalSales - $totalPayments;

        return view(
            'customers.statement',
            compact(
                'customer',
                'transactions',
                'totalSales',
                'totalPayments',
                'outstanding'
            )
        );
    }

    public function storePayment(Request $request, int $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
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

                if ($request->method === 'cash') {
                    $paymentLedgerId = 1;
                    $paymentSubLedgerId = null;
                } else {
                    $paymentLedgerId = 2;
                    $paymentSubLedgerId = null;
                }

                Accounting::postJournal([
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
                            'ledger_id' => 3,
                            'sub_ledger_id' =>  $customer->receivable_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $amount,
                        ],
                    ]
                ]);
            });


            return back()->with('success', 'Payment added successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Unable to process the payment.');
        }
    }
}
