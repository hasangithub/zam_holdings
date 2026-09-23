<?php

namespace App\Http\Controllers;

use App\Accounting\Accounting;
use App\Models\AccountGroup;
use App\Models\FixedAsset;
use App\Models\FixedAssetPayment;
use App\Models\JournalEntryReference;
use App\Models\Ledger;
use App\Models\SubLedger;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::with('supplier')
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->get();

        return view('fixed-assets.index', compact('assets'));
    }


    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();

        $cashLedger = Ledger::where('id', '1')
            ->firstOrFail();


        $fixedAssetLedgers = Ledger::where('account_group_id', '2')
            ->get();

        $cashBooks = SubLedger::where('ledger_id', $cashLedger->id)
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view(
            'fixed-assets.create',
            compact('suppliers', 'cashBooks', 'fixedAssetLedgers')
        );
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'payment_sub_ledger_id' => ['nullable', 'exists:sub_ledgers,id'],
            'fixed_asset_ledger_id' => ['required', 'exists:ledgers,id'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request) {

            $asset = FixedAsset::create([
                'branch_id' => auth()->user()->branch_id,
                'asset_code' => 'FA-' . time(),
                'name' => $request->name,
                'purchase_date' => $request->purchase_date,
                'amount' => $request->amount,
                'supplier_id' => $request->supplier_id,
                'description' => $request->description,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);


            /*
             * PAYMENT NOW
             */
            if ($request->payment_sub_ledger_id) {

                $cashBook = SubLedger::where('id', $request->payment_sub_ledger_id)
                    ->where('ledger_id', Ledger::where('id', '1')->value('id'))
                    ->where('branch_id', auth()->user()->branch_id)
                    ->firstOrFail();

                $journal = Accounting::postJournal([
                    'branch_id' => auth()->user()->branch_id,
                    'date' => $request->purchase_date,
                    'description' => 'Fixed Asset - ' . $asset->name,
                    'entries' => [

                        [
                            'ledger_id' => $request->fixed_asset_ledger_id,
                            'sub_ledger_id' => null,
                            'debit' => $request->amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => $cashBook->ledger_id,
                            'sub_ledger_id' => $cashBook->id,
                            'debit' => 0,
                            'credit' => $request->amount,
                        ],

                    ],
                ]);

                FixedAssetPayment::create([
                    'fixed_asset_id' => $asset->id,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $request->amount,
                    'payment_date' => $request->purchase_date,
                    'payment_sub_ledger_id' => $cashBook->id,
                    'note' => 'Initial payment',
                    'created_by' => auth()->id(),
                ]);
            } else {

                /*
                 * CREDIT PURCHASE
                 */

                $supplier = Supplier::findOrFail(
                    $request->supplier_id
                );

                $journal = Accounting::postJournal([
                    'branch_id' => auth()->user()->branch_id,
                    'date' => $request->purchase_date,
                    'description' => 'Fixed Asset - ' . $asset->name,
                    'entries' => [

                        [
                            'ledger_id' => $request->fixed_asset_ledger_id,
                            'sub_ledger_id' => null,
                            'debit' => $request->amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 7,
                            'sub_ledger_id' => $supplier->liability_sub_ledger_id,
                            'debit' => 0,
                            'credit' => $request->amount,
                        ],

                    ],
                ]);
            }

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => FixedAsset::class,
                'model_id' => $asset->id,
                'action' => 'created',
            ]);
        });

        return redirect()
            ->route('fixed-assets.index')
            ->with('success', 'Fixed asset added successfully.');
    }
}
