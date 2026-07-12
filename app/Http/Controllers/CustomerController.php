<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalesPayment;
use Illuminate\Http\Request;

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
        Customer::create($request->all());
        return redirect()->route('customers.index');
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

    public function storePayment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'amount' => 'required|numeric|min:0.01'
        ]);

        SalesPayment::create([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date ?? now(),
            'remarks' => $request->remarks
        ]);

        return back()->with('success', 'Payment added successfully');
    }
}
