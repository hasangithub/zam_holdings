<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseInventory;
use App\Models\PurchaseInventoryPayment;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use Illuminate\Http\Request;

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
        Supplier::create($request->all());
        return redirect()->route('suppliers.index');
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
        PurchasePayment::create([
            'supplier_id' => $id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'method' => $request->method,
            'note' => $request->note,
        ]);

        return back()->with('success', 'Payment added successfully');
    }

    public function storeInventoryPayment(Request $request, $id)
    {
        PurchaseInventoryPayment::create([
            'supplier_id' => $id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'method' => $request->method,
            'note' => $request->note,
        ]);

        return back()->with('success', 'Payment added successfully');
    }
}
