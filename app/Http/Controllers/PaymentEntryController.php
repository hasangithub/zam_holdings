<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting as AppAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SubLedger;

use App\Models\SalesPayment;
use App\Models\PurchasePayment;
use App\Models\PurchaseInventoryPayment;
use App\Models\FixedAssetPayment;

use App\Models\RequestToken;

use App\Models\Accounting;

class PaymentEntryController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        // Cash book sub-ledgers
        $cashBooks = SubLedger::where('ledger_id', 1)
            ->orderBy('name')
            ->get();

        return view('payment-entries.index', compact('cashBooks'));
    }

    /**
     * Load customers or suppliers according to payment type.
     */
    public function getParties(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:sales,purchase,inventory,asset'],
        ]);

        $branchId = auth()->user()->branch_id;

        if ($request->type === 'sales') {

            $parties = Customer::where('branch_id', $branchId)
                ->orderBy('name')
                ->get(['id', 'name']);

        } else {

            $parties = Supplier::where('branch_id', $branchId)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return response()->json($parties);
    }

    /**
     * Centralized payment store.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:sales,purchase,inventory,asset'],
            'party_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'sub_ledger_id' => ['required', 'exists:sub_ledgers,id'],
            'method' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'request_token' => ['required', 'uuid'],
        ]);

        try {

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;
                $amount = (float) $request->amount;

                /*
                |--------------------------------------------------------------------------
                | Check duplicate request
                |--------------------------------------------------------------------------
                */

                $existing = RequestToken::where(
                    'token',
                    $request->request_token
                )->lockForUpdate()->first();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'duplicate' => 'This transaction has already been processed.'
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Cash Book
                |--------------------------------------------------------------------------
                */

                $cashBook = SubLedger::where('id', $request->sub_ledger_id)
                    ->where('ledger_id', 1)
                    ->first();

                if (!$cashBook) {
                    throw ValidationException::withMessages([
                        'sub_ledger_id' => 'Please select a valid cash book.'
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create request token
                |--------------------------------------------------------------------------
                */

                RequestToken::create([
                    'token' => $request->request_token,
                    'module' => 'Central Payment - ' . ucfirst($request->type),
                    'action' => 'create',
                    'user_id' => auth()->id(),
                ]);

                $cashLedgerId = 1;
                $cashSubLedgerId = $cashBook->id;

                /*
                |--------------------------------------------------------------------------
                | CUSTOMER PAYMENT - SALES
                |--------------------------------------------------------------------------
                */

                if ($request->type === 'sales') {

                    $customer = Customer::where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->findOrFail($request->party_id);

                    if (!$customer->receivable_sub_ledger_id) {
                        throw ValidationException::withMessages([
                            'party_id' => 'This customer does not have a receivable sub-ledger.'
                        ]);
                    }

                    SalesPayment::create([
                        'customer_id' => $customer->id,
                        'amount' => $amount,
                        'payment_date' => $request->payment_date,
                        'payment_method' => $request->method,
                        'note' => $request->note,
                    ]);

                    AppAccounting::postJournal([
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
                }

                /*
                |--------------------------------------------------------------------------
                | SUPPLIER PAYMENTS
                |--------------------------------------------------------------------------
                */

                else {

                    $supplier = Supplier::where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->findOrFail($request->party_id);

                    if (!$supplier->liability_sub_ledger_id) {
                        throw ValidationException::withMessages([
                            'party_id' => 'This supplier does not have a liability sub-ledger.'
                        ]);
                    }

                    $paymentModel = null;
                    $paymentDescription = '';

                    switch ($request->type) {

                        case 'purchase':

                            $paymentModel = PurchasePayment::class;
                            $paymentDescription = 'Purchase Payment - ';
                            break;

                        case 'inventory':

                            $paymentModel = PurchaseInventoryPayment::class;
                            $paymentDescription = 'Inventory Purchase Payment - ';
                            break;

                        case 'asset':

                            $paymentModel = FixedAssetPayment::class;
                            $paymentDescription = 'Fixed Asset Payment - ';
                            break;
                    }

                    $paymentData = [
                        'supplier_id' => $supplier->id,
                        'amount' => $amount,
                        'payment_date' => $request->payment_date,
                        'note' => $request->note,
                    ];

                    // Fixed asset payment has a separate cash sub-ledger column.
                    if ($request->type === 'asset') {
                        $paymentData['payment_sub_ledger_id'] = $cashSubLedgerId;
                    }

                    $paymentModel::create($paymentData);

                    AppAccounting::postJournal([
                        'branch_id' => $branchId,
                        'date' => $request->payment_date,
                        'description' => $paymentDescription . $supplier->name,

                        'entries' => [
                            [
                                'ledger_id' => 8,
                                'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                                'debit' => $amount,
                                'credit' => 0,
                            ],
                            [
                                'ledger_id' => $cashLedgerId,
                                'sub_ledger_id' => $cashSubLedgerId,
                                'debit' => 0,
                                'credit' => $amount,
                            ],
                        ]
                    ]);
                }
            });

            return redirect()
                ->route('payment-entries.index')
                ->with('success', 'Payment added successfully.');

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Unable to process the payment.');
        }
    }
}