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
        $suppliers = Supplier::where('branch_id', auth()->user()->branch_id)
            ->where('supplier_type', 'Asset Providers')
            ->orderBy('name')
            ->get();

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
                'fixed_asset_ledger_id' => $request->fixed_asset_ledger_id,
                'asset_sub_ledger_id' => null,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $assetSubLedger = SubLedger::create([
                'branch_id' => auth()->user()->branch_id,
                'ledger_id' => $request->fixed_asset_ledger_id,
                'name' => $asset->name,
            ]);

            $asset->update([
                'asset_sub_ledger_id' => $assetSubLedger->id,
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
                            'sub_ledger_id' => $assetSubLedger->id,
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
                            'sub_ledger_id' => $assetSubLedger->id,
                            'debit' => $request->amount,
                            'credit' => 0,
                        ],

                        [
                            'ledger_id' => 8,
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

    public function show($id)
    {
        $asset = FixedAsset::with([
            'supplier',
            'fixedAssetLedger',
            'assetSubLedger',
            'openingJournalEntry',
        ])
            ->where('branch_id', auth()->user()->branch_id)
            ->findOrFail($id);

        return view('fixed-assets.show', compact('asset'));
    }

    public function storeOpeningBalance(Request $request, $id)
    {
        $request->validate([
            'opening_date' => ['required', 'date'],
            'opening_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($request, $id) {

            $branchId = auth()->user()->branch_id;

            $asset = FixedAsset::where('branch_id', $branchId)
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($asset->opening_journal_entry_id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'opening_amount' => 'An opening balance has already been recorded for this asset.',
                ]);
            }

            if (!$asset->fixed_asset_ledger_id || !$asset->asset_sub_ledger_id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'opening_amount' => 'The asset category ledger or asset sub-ledger is missing. Update the asset ledger details first.',
                ]);
            }

            

            // Confirm the asset sub-ledger belongs to its selected category ledger.
            $assetSubLedger = SubLedger::where('id', $asset->asset_sub_ledger_id)
                ->where('ledger_id', $asset->fixed_asset_ledger_id)
                ->where('branch_id', $branchId)
                ->firstOrFail();

            $journal = Accounting::postJournal([
                'branch_id' => $branchId,
                'date' => $request->opening_date,
                'description' => 'Fixed Asset Opening Balance - ' . $asset->name,
                'created_by' => auth()->id(),
                'entries' => [
                    [
                        'ledger_id' => $asset->fixed_asset_ledger_id,
                        'sub_ledger_id' => $assetSubLedger->id,
                        'debit' => $request->opening_amount,
                        'credit' => 0,
                    ],
                    [
                        'ledger_id' => 17,
                        'sub_ledger_id' => null,
                        'debit' => 0,
                        'credit' => $request->opening_amount,
                    ],
                ],
            ]);

            $asset->update([
                'opening_journal_entry_id' => $journal->id,
            ]);

            JournalEntryReference::create([
                'journal_entry_id' => $journal->id,
                'model_type' => FixedAsset::class,
                'model_id' => $asset->id,
                'action' => 'created',
            ]);
        });

        return redirect()
            ->route('fixed-assets.show', $id)
            ->with('success', 'Fixed asset opening balance recorded successfully.');
    }
}


// asset debit equity credit