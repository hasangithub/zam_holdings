<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\FixedAssetPayment;
use App\Models\FixedAsset;
use App\Models\Purchase;
use App\Models\PurchaseAmendment;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryPayment;
use App\Models\PurchasePayment;
use App\Models\RequestToken;
use App\Models\SubLedger;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('branch_id', auth()->user()->branch_id)->latest()->get();
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
            'supplier_type' => 'required|in:Trading Goods,Packing Material,Asset Providers,Others',
        ]);


        DB::transaction(function () use ($request) {

            /*
        |--------------------------------------------------------------------------
        | Create Supplier
        |--------------------------------------------------------------------------
        */

            $supplier = Supplier::create($request->all() + [
                'branch_id' => auth()->user()->branch_id,
            ]);


            /*
        |--------------------------------------------------------------------------
        | Create Liability Sub Ledger
        |--------------------------------------------------------------------------
        */
            if ($supplier->supplier_type == 'Trading Goods') {
                $subLedger = SubLedger::create([

                    'ledger_id' => 8,
                    'name'      => $supplier->name,
                ]);
            } else if ($supplier->supplier_type == 'Packing Material') {
                $subLedger = SubLedger::create([

                    'ledger_id' => 8,
                    'name'      => $supplier->name,
                ]);
            } else if ($supplier->supplier_type == 'Asset Providers') {
                $subLedger = SubLedger::create([

                    'ledger_id' => 10,
                    'name'      => $supplier->name,
                ]);
            } else if ($supplier->supplier_type == 'Others') {
                $subLedger = SubLedger::create([

                    'ledger_id' => 8,
                    'name'      => $supplier->name,
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Attach Sub Ledger to Supplier
        |--------------------------------------------------------------------------
        */

            $supplier->update([
                'liability_sub_ledger_id' => $subLedger->id,
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
        if ((int) $supplier->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to edit this supplier.');
        }
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        if ((int) $supplier->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed to update this supplier.');
        }

        $supplier->update($request->all());

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

    public function statement(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        if ((int) $supplier->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'You are not allowed.');
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
    | Trading Goods
    |--------------------------------------------------------------------------
    */

        if ($supplier->supplier_type == 'Trading Goods') {

            /*
        |--------------------------------------------------------------------------
        | Purchases
        |--------------------------------------------------------------------------
        */

            $purchases = Purchase::where(
                'supplier_id',
                $id
            )->get();


            foreach ($purchases as $purchase) {

                $ledger->push([

                    'date' => $purchase->purchase_date,

                    'module' => 'Purchase',

                    'type' => 'Invoice',

                    'debit' => (float) $purchase->total,

                    'credit' => 0,

                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Purchase Amendments
        |--------------------------------------------------------------------------
        */

            $amendments = PurchaseAmendment::where(
                'supplier_id',
                $id
            )
                ->where('status', 'approved')
                ->get();


            foreach ($amendments as $amendment) {

                $amount = (float) $amendment->amount;


                /*
            |--------------------------------------------------------------------------
            | Positive Amendment
            |--------------------------------------------------------------------------
            |
            | Supplier payable increases
            |
            */

                if ($amount > 0) {

                    $debit = $amount;

                    $credit = 0;
                }

                /*
            |--------------------------------------------------------------------------
            | Negative Amendment
            |--------------------------------------------------------------------------
            |
            | Supplier payable decreases
            |
            */ else {

                    $debit = 0;

                    $credit = abs($amount);
                }


                $ledger->push([

                    'date' => $amendment->amendment_date,

                    'module' => 'Purchase',

                    'type' => 'Amendment',

                    'reference' => 'AMD-' . $amendment->id,

                    'description' => $amendment->reason,

                    'debit' => $debit,

                    'credit' => $credit,

                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

            $payments = PurchasePayment::where(
                'supplier_id',
                $id
            )->get();


            foreach ($payments as $payment) {

                $ledger->push([

                    'date' => $payment->payment_date,

                    'module' => 'Purchase',

                    'type' => 'Payment',

                    'debit' => 0,

                    'credit' => (float) $payment->amount,

                ]);
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Asset Providers
    |--------------------------------------------------------------------------
    */ elseif ($supplier->supplier_type == 'Asset Providers') {

            $purchases = FixedAsset::where(
                'supplier_id',
                $id
            )->get();


            $payments = FixedAssetPayment::where(
                'supplier_id',
                $id
            )->get();


            foreach ($purchases as $purchase) {

                $ledger->push([

                    'date' => $purchase->purchase_date,

                    'module' => 'Fixed Asset',

                    'type' => 'Invoice',

                    'debit' => (float) $purchase->amount,

                    'credit' => 0,

                ]);
            }


            foreach ($payments as $payment) {

                $ledger->push([

                    'date' => $payment->payment_date,

                    'module' => 'Fixed Asset',

                    'type' => 'Payment',

                    'debit' => 0,

                    'credit' => (float) $payment->amount,

                ]);
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Other Supplier Types
    |--------------------------------------------------------------------------
    */ else {

            $purchases = PurchaseInventory::where(
                'supplier_id',
                $id
            )->get();


            $payments = PurchaseInventoryPayment::where(
                'supplier_id',
                $id
            )->get();


            foreach ($purchases as $purchase) {

                $ledger->push([

                    'date' => $purchase->purchase_date,

                    'module' => 'Inventory',

                    'type' => 'Invoice',

                    'debit' => (float) $purchase->total,

                    'credit' => 0,

                ]);
            }


            foreach ($payments as $payment) {

                $ledger->push([

                    'date' => $payment->payment_date,

                    'module' => 'Inventory',

                    'type' => 'Payment',

                    'debit' => 0,

                    'credit' => (float) $payment->amount,

                ]);
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    |
    | Date first.
    | Reference/type gives deterministic ordering when two transactions
    | have the same date.
    |
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
    | Filter + Opening Balance
    |--------------------------------------------------------------------------
    */

        if (!$all) {

            $filtered = collect();


            foreach ($ledger as $row) {

                /*
            |--------------------------------------------------------------------------
            | Before From Date
            |--------------------------------------------------------------------------
            */

                if ($row['date'] < $fromDate) {

                    $openingBalance +=
                        $row['debit'] - $row['credit'];
                }


                /*
            |--------------------------------------------------------------------------
            | From Date -> To Date
            |--------------------------------------------------------------------------
            */ elseif (
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
    | Running Balance
    |--------------------------------------------------------------------------
    */

        $running = $openingBalance;


        $ledger = $ledger->map(function ($row) use (&$running) {

            $running +=
                $row['debit'] -
                $row['credit'];


            $row['balance'] = $running;


            return $row;
        });


        /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

        $periodPurchase = $ledger->sum('debit');

        $periodPaid = $ledger->sum('credit');


        $balance =
            $openingBalance
            + $periodPurchase
            - $periodPaid;


        /*
    |--------------------------------------------------------------------------
    | All Transactions
    |--------------------------------------------------------------------------
    */

        if ($all) {

            $balance =
                $ledger->last()['balance'] ?? 0;
        }


        /*
    |--------------------------------------------------------------------------
    | Payment Sub Ledgers
    |--------------------------------------------------------------------------
    */

        $paymentSubLedgers = SubLedger::where(
            'ledger_id',
            1
        )->get();


        return view(
            'suppliers.statement',
            compact(
                'supplier',
                'ledger',
                'balance',
                'openingBalance',
                'periodPurchase',
                'periodPaid',
                'fromDate',
                'toDate',
                'all',
                'paymentSubLedgers'
            )
        );
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
            'payment_date' => ['required', 'date',],
            'sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => ['nullable', 'string', 'max:1000',],
            'request_token' => ['required', 'uuid'],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $supplier = Supplier::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                $existing = RequestToken::where(
                    'token',
                    $request->request_token
                )->first();

                if ($existing) {

                    throw ValidationException::withMessages(['duplicate' => 'This transaction has already been processed.']);
                }

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This supplier does not have a liability sub-ledger.']);
                }

                $token = RequestToken::create([
                    'token' => $request->request_token,
                    'module' => 'Supplier Payment',
                    'action' => 'create',
                    'user_id' => auth()->id(),
                ]);

                $payment = PurchasePayment::create([

                    'supplier_id' => $supplier->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'note' => $request->note,
                ]);

                $paymentLedgerId = 1;
                $paymentSubLedgerId = $request->sub_ledger_id;

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 8,
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
            'sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => ['nullable', 'string', 'max:1000',],
            'request_token' => ['required', 'uuid'],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $supplier = Supplier::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                $existing = RequestToken::where(
                    'token',
                    $request->request_token
                )->first();

                if ($existing) {

                    throw ValidationException::withMessages(['duplicate' => 'This transaction has already been processed.']);
                }

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This supplier does not have a liability sub-ledger.']);
                }

                $token = RequestToken::create([
                    'token' => $request->request_token,
                    'module' => 'Supplier Payment',
                    'action' => 'create',
                    'user_id' => auth()->id(),
                ]);

                $payment = PurchaseInventoryPayment::create([

                    'supplier_id' => $supplier->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'note' => $request->note,
                ]);


                $paymentLedgerId = 1;
                $paymentSubLedgerId = $request->sub_ledger_id;

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 8,
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

    public function storeAssetPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0',],
            'payment_date' => ['required', 'date',],
            'sub_ledger_id' => 'required|exists:sub_ledgers,id',
            'note' => ['nullable', 'string', 'max:1000',],
            'request_token' => ['required', 'uuid'],
        ]);

        try {

            DB::transaction(function () use ($request, $id) {

                $supplier = Supplier::lockForUpdate()->findOrFail($id);
                $amount = (float) $request->amount;
                $branchId = auth()->user()->branch_id;

                $existing = RequestToken::where(
                    'token',
                    $request->request_token
                )->first();

                if ($existing) {

                    throw ValidationException::withMessages(['duplicate' => 'This transaction has already been processed.']);
                }

                if (!$supplier->liability_sub_ledger_id) {
                    throw ValidationException::withMessages(['amount' => 'This supplier does not have a non current liability sub-ledger.']);
                }

                $token = RequestToken::create([
                    'token' => $request->request_token,
                    'module' => 'Supplier Payment',
                    'action' => 'create',
                    'user_id' => auth()->id(),
                ]);

                $payment = FixedAssetPayment::create([

                    'supplier_id' => $supplier->id,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'payment_sub_ledger_id' => $request->sub_ledger_id,
                    'note' => $request->note,
                ]);


                $paymentLedgerId = 1;
                $paymentSubLedgerId = $request->sub_ledger_id;

                Accounting::postJournal([
                    'branch_id' => $branchId,
                    'date' => $request->payment_date,
                    'description' => 'Payment for purchase - ' . $supplier->name,

                    'entries' => [
                        [
                            'ledger_id' => 8,
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
