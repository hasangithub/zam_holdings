<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use App\Models\SubLedger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubLedgerController extends Controller
{
    public function index()
    {
        $subLedgers = SubLedger::with([
            'ledger.accountGroup.accountType'
        ])
        ->paginate(25);

        return view(
            'accounting.sub-ledgers.index',
            compact('subLedgers')
        );
    }

    public function create()
    {
        $ledgers = Ledger::with('accountGroup')
            ->get();

        return view(
            'accounting.sub-ledgers.create',
            compact('ledgers')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ledger_id' => [
                'required',
                'exists:ledgers,id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],
        ]);

        $validated['created_by'] = auth()->id();

        SubLedger::create($validated);

        return redirect()
            ->route('accounting.sub-ledgers.index')
            ->with('success', 'Sub ledger created successfully.');
    }

    public function edit(SubLedger $subLedger)
    {
        $ledgers = Ledger::where('status', true)
            ->with('accountGroup')
            ->orderBy('code')
            ->get();

        return view(
            'accounting.sub-ledgers.edit',
            compact('subLedger', 'ledgers')
        );
    }

    public function update(
        Request $request,
        SubLedger $subLedger
    ) {
        $validated = $request->validate([
            'ledger_id' => [
                'required',
                'exists:ledgers,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sub_ledgers', 'code')
                    ->ignore($subLedger->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $validated['updated_by'] = auth()->id();

        $subLedger->update($validated);

        return redirect()
            ->route('accounting.sub-ledgers.index')
            ->with('success', 'Sub ledger updated successfully.');
    }

    public function destroy(SubLedger $subLedger)
    {
        if ($subLedger->journalDetails()->exists()) {
            return back()->with(
                'error',
                'This sub ledger cannot be deleted because transactions exist.'
            );
        }

        $subLedger->deleted_by = auth()->id();
        $subLedger->save();

        $subLedger->delete();

        return back()->with(
            'success',
            'Sub ledger deleted successfully.'
        );
    }
}