<?php

namespace App\Http\Controllers;


use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Branch;
use App\Models\Ledger;
use App\Models\SubLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $accountTypes = AccountType::with([
            'accountGroups',
            'accountGroups.ledgers',
            'accountGroups.ledgers.subLedgers'
        ])
            ->get();

        $statistics = [
            'account_types' => AccountType::count(),
            'account_groups' => AccountGroup::count(),
            'ledgers'       => Ledger::count(),
            'sub_ledgers'   => SubLedger::count(),
        ];

        return view(
            'accounting.chart-of-accounts.index',
            compact('accountTypes', 'statistics')
        );
    }

    public function create()
    {
        $accountTypes = AccountType::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('accounting.chart-of-accounts.account-setup', compact('accountTypes', 'branches'));
    }

    public function groups(AccountType $accountType)
    {
        return response()->json(
            AccountGroup::where('account_type_id', $accountType->id)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function ledgers(AccountGroup $accountGroup)
    {
        return response()->json(
            Ledger::where('account_group_id', $accountGroup->id)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function subLedgers(Ledger $ledger)
    {
        return response()->json(
            SubLedger::where('ledger_id', $ledger->id)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_type_id' => ['required', 'exists:account_types,id'],

            'group_action' => ['required', 'in:existing,new'],
            'group_id' => ['nullable', 'exists:account_groups,id'],
            'group_name' => ['required_if:group_action,new', 'nullable', 'string', 'max:255'],

            'ledger_action' => ['required', 'in:none,existing,new'],
            'ledger_id' => ['nullable', 'exists:ledgers,id'],
            'ledger_name' => ['required_if:ledger_action,new', 'nullable', 'string', 'max:255'],

            'subledger_name' => ['nullable', 'string', 'max:255'],
            'subledger_branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        DB::transaction(function () use ($request) {

            // 1. Resolve or create account group
            if ($request->group_action === 'new') {
                $group = AccountGroup::create([
                    'account_type_id' => $request->account_type_id,
                    'name' => trim($request->group_name),
                ]);
            } else {
                if (!$request->group_id) {
                    throw ValidationException::withMessages([
                        'group_id' => 'Please select an account group.',
                    ]);
                }

                $group = AccountGroup::where('id', $request->group_id)
                    ->where('account_type_id', $request->account_type_id)
                    ->first();

                if (!$group) {
                    throw ValidationException::withMessages([
                        'group_id' => 'The selected group does not belong to this account type.',
                    ]);
                }
            }

            // 2. Resolve or create ledger
            $ledger = null;

            if ($request->ledger_action === 'new') {
                $ledger = Ledger::create([
                    'account_group_id' => $group->id,
                    'name' => trim($request->ledger_name),
                ]);
            } elseif ($request->ledger_action === 'existing') {
                if (!$request->ledger_id) {
                    throw ValidationException::withMessages([
                        'ledger_id' => 'Please select a ledger.',
                    ]);
                }

                $ledger = Ledger::where('id', $request->ledger_id)
                    ->where('account_group_id', $group->id)
                    ->first();

                if (!$ledger) {
                    throw ValidationException::withMessages([
                        'ledger_id' => 'The selected ledger does not belong to this account group.',
                    ]);
                }
            }

            // 3. Create subledger only when a name is entered
            $subledgerName = trim((string) $request->input('subledger_name', ''));

            if ($subledgerName !== '') {

                if (!$ledger) {
                    throw ValidationException::withMessages([
                        'subledger_name' => 'Select or create a ledger before adding a subledger.',
                    ]);
                }

                $subledgerData = [
                    'ledger_id' => $ledger->id,
                    'name' => $subledgerName,
                ];

                // Ledger ID 1 requires a branch selection
                if ((int) $ledger->id === 1) {

                    Validator::make($request->all(), [
                        'subledger_branch_id' => [
                            'required',
                            'exists:branches,id',
                        ],
                    ])->validate();

                    $subledgerData['branch_id'] =
                        $request->input('subledger_branch_id');
                } else {
                    $subledgerData['branch_id'] = null;
                }

                SubLedger::create($subledgerData);
            }
        });

        return redirect()
            ->route('account-setup.create')
            ->with('success', 'Account setup saved successfully.');
    }
}
