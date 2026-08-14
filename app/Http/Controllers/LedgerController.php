<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LedgerController extends Controller
{
    public function index()
    {
        $ledgers = Ledger::with([
            'accountGroup.accountType'
        ])
        ->paginate(25);

        return view(
            'accounting.ledgers.index',
            compact('ledgers')
        );
    }

    public function create()
    {
        $accountGroups = AccountGroup::with('accountType')
            ->get();

        return view(
            'accounting.ledgers.create',
            compact('accountGroups')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_group_id' => [
                'required',
                'exists:account_groups,id',
            ],


            'name' => [
                'required',
                'string',
                'max:150',
            ],
        ]);

        Ledger::create($validated);

        return redirect()
            ->route('accounting.ledgers.index')
            ->with('success', 'Ledger created successfully.');
    }

    public function edit(Ledger $ledger)
    {
        $accountGroups = AccountGroup::where('status', true)
            ->with('accountType')
            ->orderBy('code')
            ->get();

        return view(
            'accounting.ledgers.edit',
            compact('ledger', 'accountGroups')
        );
    }

    public function update(
        Request $request,
        Ledger $ledger
    ) {
        $validated = $request->validate([
            'account_group_id' => [
                'required',
                'exists:account_groups,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ledgers', 'code')
                    ->ignore($ledger->id),
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

        $ledger->update($validated);

        return redirect()
            ->route('accounting.ledgers.index')
            ->with('success', 'Ledger updated successfully.');
    }

    public function destroy(Ledger $ledger)
    {
        if ($ledger->subLedgers()->exists()) {
            return back()->with(
                'error',
                'This ledger cannot be deleted because sub ledgers exist under it.'
            );
        }

        if ($ledger->journalDetails()->exists()) {
            return back()->with(
                'error',
                'This ledger cannot be deleted because transactions exist.'
            );
        }

        $ledger->deleted_by = auth()->id();
        $ledger->save();

        $ledger->delete();

        return back()->with(
            'success',
            'Ledger deleted successfully.'
        );
    }

    public function byAccountGroup(AccountGroup $accountGroup)
    {
        $ledgers = Ledger::where('account_group_id', $accountGroup->id)
            ->where('status', true)
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json($ledgers);
    }
}