<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Purchase;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryPayment;
use App\Models\PurchasePayment;
use App\Models\SubLedger;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
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

            $supplier = Supplier::create($request->all());


            /*
        |--------------------------------------------------------------------------
        | Create Liability Sub Ledger
        |--------------------------------------------------------------------------
        */

            $subLedger = SubLedger::create([

                'ledger_id' => 5,
                'name'      => $supplier->name,
            ]);


            /*
        |--------------------------------------------------------------------------
        | Attach Sub Ledger to Supplier
        |--------------------------------------------------------------------------
        */

            $supplier->update([

                'liability_sub_ledger_id' =>
                $subLedger->id,

            ]);
        });


        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier created successfully.'
            );
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        Supplier::findOrFail($id)->update($request->all());
        return redirect()->route('suppliers.index');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return back();
    }

    // public function statement($id)
    // {
    //     $supplier = Supplier::findOrFail($id);

    //     $purchases = Purchase::where('supplier_id', $id)->get();

    //     $payments = PurchasePayment::where('supplier_id', $id)->get();

    //     $totalPurchase = $purchases->sum('total');
    //     $totalPaid = $payments->sum('amount');

    //     $balance = $totalPurchase - $totalPaid;

    //     // build ledger
    //     $ledger = collect();

    //     foreach ($purchases as $p) {
    //         $ledger->push([
    //             'date' => $p->purchase_date,
    //             'type' => 'Purchase',
    //             'debit' => $p->total,
    //             'credit' => 0,
    //         ]);
    //     }

    //     foreach ($payments as $pay) {
    //         $ledger->push([
    //             'date' => $pay->payment_date,
    //             'type' => 'Payment',
    //             'debit' => 0,
    //             'credit' => $pay->amount,
    //         ]);
    //     }

    //     $ledger = $ledger->sortBy('date');

    //     $running = 0;

    //     foreach ($ledger as $item) {
    //         $running += $item['debit'] - $item['credit'];
    //         $item['balance'] = $running;
    //     }

    //     return view('suppliers.statement', compact(
    //         'supplier',
    //         'ledger',
    //         'balance',
    //         'totalPurchase',
    //         'totalPaid'
    //     ));
    // }

    public function statement($id)
    {
        $supplier = Supplier::findOrFail($id);

        $purchases = Purchase::where('supplier_id', $id)->get();

        $purchasePayments = PurchasePayment::where('supplier_id', $id)->get();

        $inventoryPurchases = PurchaseInventory::where('supplier_id', $id)->get();

        $inventoryPayments = PurchaseInventoryPayment::where('supplier_id', $id)->get();

        $totalPurchase =
            $purchases->sum('total')
            +
            $inventoryPurchases->sum('total');

        $totalPaid =
            $purchasePayments->sum('amount')
            +
            $inventoryPayments->sum('amount');

        $balance = $totalPurchase - $totalPaid;

        $ledger = collect();

        // PURCHASES
        foreach ($purchases as $p) {

            $ledger->push([
                'date' => $p->purchase_date,
                'module' => 'Purchase',
                'type' => 'Invoice',
                'debit' => $p->total,
                'credit' => 0,
            ]);
        }

        // PURCHASE PAYMENTS
        foreach ($purchasePayments as $pay) {

            $ledger->push([
                'date' => $pay->payment_date,
                'module' => 'Purchase',
                'type' => 'Payment',
                'debit' => 0,
                'credit' => $pay->amount,
            ]);
        }

        // INVENTORY PURCHASES
        foreach ($inventoryPurchases as $inv) {

            $ledger->push([
                'date' => $inv->purchase_date,
                'module' => 'Inventory',
                'type' => 'Invoice',
                'debit' => $inv->total,
                'credit' => 0,
            ]);
        }

        // INVENTORY PAYMENTS
        foreach ($inventoryPayments as $pay) {

            $ledger->push([
                'date' => $pay->payment_date,
                'module' => 'Inventory',
                'type' => 'Payment',
                'debit' => 0,
                'credit' => $pay->amount,
            ]);
        }

        $ledger = $ledger->sortBy('date')->values();

        $running = 0;

        foreach ($ledger as &$item) {

            $running += ($item['debit'] - $item['credit']);

            $item['balance'] = $running;
        }

        return view('suppliers.statement', compact(
            'supplier',
            'ledger',
            'balance',
            'totalPurchase',
            'totalPaid'
        ));
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
            'payment_date' => ['required', 'date',],
            'method' => ['required', 'in:Cash,Bank,Cheque',],
            'note' => ['nullable', 'string', 'max:1000',],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $supplier = Supplier::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This supplier does not have a liability sub-ledger.']);
                }

                $payment = PurchasePayment::create([

                    'supplier_id' => $supplier->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'method' => $request->method,
                    'note' => $request->note,
                ]);

                if ($request->method === 'Cash') {
                    $paymentLedgerId = 1;
                    $paymentSubLedgerId = null;
                } else {
                    $paymentLedgerId = 2;
                    $paymentSubLedgerId = null;
                }

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 5,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => $paymentLedgerId,
                            'sub_ledger_id' =>  $paymentSubLedgerId,
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

    public function storeInventoryPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
            'payment_date' => ['required', 'date',],
            'method' => ['required', 'in:Cash,Bank,Cheque',],
            'note' => ['nullable', 'string', 'max:1000',],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $supplier = Supplier::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This supplier does not have a liability sub-ledger.']);
                }

                $payment = PurchaseInventoryPayment::create([

                    'supplier_id' => $supplier->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'method' => $request->method,
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
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 5,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => $amount,
                            'credit' => 0,
                        ],
                        [
                            'ledger_id' => $paymentLedgerId,
                            'sub_ledger_id' =>  $paymentSubLedgerId,
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
