<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\Freight;
use App\Models\FreightPayment;
use App\Models\FreightService;
use App\Models\Sale;
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
            ->paginate(20);

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



        return view('freight_expenses.show', compact(
            'freight'
        ));
    }

    public function payment(Request $request, $id)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'note' => 'nullable|string',
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
                'date' => $request->purchase_date,
                'description' => 'Freight Payment: ' . $freight->service->name . ' for Sale ID: ' . $freight->sale_id,

                'entries' => [
                    [
                        'ledger_id' => 1,
                        'sub_ledger_id' => null,
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
}
