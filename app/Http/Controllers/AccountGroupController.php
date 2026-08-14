<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountGroupController extends Controller
{
    public function index()
    {
        $accountGroups = AccountGroup::with('accountType')
            ->orderBy('code')
            ->paginate(25);

        return view(
            'accounting.account-groups.index',
            compact('accountGroups')
        );
    }

    public function create()
    {
        $accountTypes = AccountType::get();

        return view(
            'accounting.account-groups.create',
            compact('accountTypes')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type_id' => [
                'required',
                'exists:account_types,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'unique:account_groups,code',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],
        ]);


        AccountGroup::create($validated);

        return redirect()
            ->route('accounting.account-groups.index')
            ->with('success', 'Account group created successfully.');
    }

    public function edit(AccountGroup $accountGroup)
    {
        $accountTypes = AccountType::orderBy('code')->get();

        return view(
            'accounting.account-groups.edit',
            compact('accountGroup', 'accountTypes')
        );
    }

    public function update(
        Request $request,
        AccountGroup $accountGroup
    ) {
        $validated = $request->validate([
            'account_type_id' => [
                'required',
                'exists:account_types,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('account_groups', 'code')
                    ->ignore($accountGroup->id),
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

        $accountGroup->update($validated);

        return redirect()
            ->route('accounting.account-groups.index')
            ->with('success', 'Account group updated successfully.');
    }

    public function destroy(AccountGroup $accountGroup)
    {
        if ($accountGroup->ledgers()->exists()) {
            return back()->with(
                'error',
                'This account group cannot be deleted because ledgers exist under it.'
            );
        }

        $accountGroup->deleted_by = auth()->id();
        $accountGroup->save();

        $accountGroup->delete();

        return back()->with(
            'success',
            'Account group deleted successfully.'
        );
    }
}