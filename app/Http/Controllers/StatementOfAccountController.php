<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\JournalEntryDetail;
use App\Models\StatementOfAccount;
use App\Models\StatementOfAccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class StatementOfAccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Access control
    |--------------------------------------------------------------------------
    */

    private function ensureAllowedBranch(): void
    {
        abort_unless(
            in_array((int) auth()->user()->branch_id, [1, 2], true),
            403,
            'You are not authorized to access Statements of Account.'
        );
    }

    private function ensureCanCreate(): void
    {
        abort_unless(
            (int) auth()->user()->branch_id === 2,
            403,
            'Only Branch 2 can create statements.'
        );
    }

    private function canViewStatement(
        StatementOfAccount $statement
    ): bool {
        $branchId = (int) auth()->user()->branch_id;

        if ($branchId === 2) {
            return (int) $statement->branch_id === 2
                && $statement->status === 'draft';
        }

        if ($branchId === 1) {
            return (int) $statement->branch_id === 2
                && $statement->status === 'submitted';
        }

        return false;
    }

    private function canEditStatement(
        StatementOfAccount $statement
    ): bool {
        return $this->canViewStatement($statement);
    }

    /*
    |--------------------------------------------------------------------------
    | Effective report date
    |--------------------------------------------------------------------------
    */

    private function effectiveAsOfDate(string $selectedDate): string
    {
        // Branch 1 and Branch 2 use the selected date.
        return Carbon::parse($selectedDate)->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->ensureAllowedBranch();

        $branchId = (int) auth()->user()->branch_id;

        $query = StatementOfAccount::query();

        if ($branchId === 2) {
            $query->where('branch_id', 2)
                ->where('status', 'draft');
        } else {
            $query->where('branch_id', 2)
                ->where('status', 'submitted');
        }

        $statements = $query
            ->orderByDesc('id')
            ->paginate(20);

        return view('statements.index', compact('statements'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create form
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $this->ensureCanCreate();

        $selectedDate = $request->input(
            'as_of_date',
            now()->toDateString()
        );

        $asOfDate = $this->effectiveAsOfDate($selectedDate);

        $report = $this->buildReport($asOfDate);

        return view('statements.create', compact(
            'selectedDate',
            'asOfDate',
            'report'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Store a new draft
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $this->ensureCanCreate();

        $validated = $request->validate([
            'as_of_date' => ['required', 'date'],
            'adjustments' => ['nullable', 'array'],
            'adjustments.*' => ['nullable', 'array'],
            'adjustments.*.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'adjustments.*.*.amount' => [
                'nullable',
                'numeric',
            ],
        ]);

        $asOfDate = $this->effectiveAsOfDate(
            $validated['as_of_date']
        );

        // Calculate the balances across all branches.
        $report = $this->buildReport($asOfDate);

        DB::transaction(function () use (
            $validated,
            $asOfDate,
            $report
        ) {
            $totalAssets = 0;
            $totalLiabilities = 0;

            foreach ($report as $sectionGroups) {
                foreach ($sectionGroups as $group) {
                    $adjustments = $validated['adjustments']
                        [$group['account_group_id']] ?? [];

                    $adjustmentTotal = $this->calculateAdjustmentTotal(
                        $adjustments
                    );

                    $adjustedTotal = $group['ledger_total']
                        + $adjustmentTotal;

                    if ($group['section'] === 'asset') {
                        $totalAssets += $adjustedTotal;
                    } else {
                        $totalLiabilities += $adjustedTotal;
                    }
                }
            }

            $statement = StatementOfAccount::create([
                'reference_no' => 'SOA-' . now()->format('YmdHis'),
                'as_of_date' => $asOfDate,
                'branch_id' => 2,
                'status' => 'draft',
                'submitted_at' => null,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'difference' => $totalAssets - $totalLiabilities,
                'created_by' => auth()->id(),
            ]);

            foreach ($report as $sectionGroups) {
                foreach ($sectionGroups as $group) {
                    $savedGroup = $statement->groups()->create([
                        'account_type_id' => $group['account_type_id'],
                        'account_group_id' => $group['account_group_id'],
                        'account_type_name' => $group['account_type_name'],
                        'account_group_name' => $group['account_group_name'],
                        'section' => $group['section'],
                        'ledger_total' => $group['ledger_total'],
                        'adjustment_total' => 0,
                        'adjusted_total' => $group['ledger_total'],
                        'ledger_balances' => $group['ledger_balances'],
                    ]);

                    $adjustments = $validated['adjustments']
                        [$group['account_group_id']] ?? [];

                    foreach ($adjustments as $adjustment) {
                        $name = trim($adjustment['name'] ?? '');
                        $amount = $adjustment['amount'] ?? null;

                        if ($name === '' || $amount === null || $amount === '') {
                            continue;
                        }

                        $savedGroup->adjustments()->create([
                            'name' => $name,
                            'amount' => $amount,
                        ]);
                    }

                    $adjustmentTotal = (float) $savedGroup
                        ->adjustments()
                        ->sum('amount');

                    $savedGroup->update([
                        'adjustment_total' => $adjustmentTotal,
                        'adjusted_total' => (float) $savedGroup->ledger_total
                            + $adjustmentTotal,
                    ]);
                }
            }
        });

        return redirect()
            ->route('statements.index')
            ->with('success', 'Statement saved as draft successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show statement
    |--------------------------------------------------------------------------
    */

    public function show(StatementOfAccount $statement)
    {
        abort_unless(
            $this->canViewStatement($statement),
            403,
            'You are not authorized to view this statement.'
        );

        $statement->load('groups.adjustments');

        return view('statements.show', compact('statement'));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit statement
    |--------------------------------------------------------------------------
    */

    public function edit(StatementOfAccount $statement)
    {
        abort_unless(
            $this->canEditStatement($statement),
            403,
            'You are not authorized to edit this statement.'
        );

        $statement->load('groups.adjustments');

        return view('statements.edit', compact('statement'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update adjustments without rebuilding snapshot balances
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        StatementOfAccount $statement
    ) {
        abort_unless(
            $this->canEditStatement($statement),
            403,
            'You are not authorized to edit this statement.'
        );

        $validated = $request->validate([
            'adjustments' => ['nullable', 'array'],
            'adjustments.*' => ['nullable', 'array'],
            'adjustments.*.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'adjustments.*.*.amount' => [
                'nullable',
                'numeric',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $statement
        ) {
            $statement = StatementOfAccount::whereKey($statement->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $this->canEditStatement($statement),
                403,
                'You are not authorized to edit this statement.'
            );

            $statement->load('groups.adjustments');

            $totalAssets = 0;
            $totalLiabilities = 0;

            foreach ($statement->groups as $group) {
                $submittedAdjustments = $validated['adjustments']
                    [$group->id] ?? [];

                $group->adjustments()->delete();

                foreach ($submittedAdjustments as $adjustment) {
                    $name = trim($adjustment['name'] ?? '');
                    $amount = $adjustment['amount'] ?? null;

                    if ($name === '' || $amount === null || $amount === '') {
                        continue;
                    }

                    $group->adjustments()->create([
                        'name' => $name,
                        'amount' => $amount,
                    ]);
                }

                $adjustmentTotal = (float) $group
                    ->adjustments()
                    ->sum('amount');

                $adjustedTotal = (float) $group->ledger_total
                    + $adjustmentTotal;

                $group->update([
                    'adjustment_total' => $adjustmentTotal,
                    'adjusted_total' => $adjustedTotal,
                ]);

                if ($group->section === 'asset') {
                    $totalAssets += $adjustedTotal;
                } else {
                    $totalLiabilities += $adjustedTotal;
                }
            }

            $statement->update([
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'difference' => $totalAssets - $totalLiabilities,
            ]);
        });

        return redirect()
            ->route('statements.show', $statement->id)
            ->with('success', 'Statement updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Branch 2 draft to Branch 1
    |--------------------------------------------------------------------------
    */

    public function submit(StatementOfAccount $statement)
    {
        abort_unless(
            (int) auth()->user()->branch_id === 2,
            403,
            'Only Branch 2 can submit statements.'
        );

        DB::transaction(function () use ($statement) {
            $statement = StatementOfAccount::whereKey($statement->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $statement->branch_id === 2
                && $statement->status === 'draft',
                403,
                'This statement cannot be submitted.'
            );

            $statement->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        });

        return redirect()
            ->route('statements.index')
            ->with('success', 'Statement submitted to Head Office successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Build report from all branches
    |--------------------------------------------------------------------------
    */

    private function buildReport(string $asOfDate): array
    {
        $balances = JournalEntryDetail::query()
            ->select(
                'ledger_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->whereHas('journalEntry', function ($query) use ($asOfDate) {
                $query->whereDate('journal_date', '<=', $asOfDate);
            })
            ->groupBy('ledger_id')
            ->get()
            ->keyBy('ledger_id');

        $accountTypes = AccountType::with('accountGroups.ledgers')
            ->get();

        $report = [
            'asset' => [],
            'liability' => [],
        ];

        foreach ($accountTypes as $accountType) {
            $typeName = strtolower(trim($accountType->name));

            if (str_contains($typeName, 'asset')) {
                $section = 'asset';
                $normalBalance = 'debit';
            } elseif (str_contains($typeName, 'liabilit')) {
                $section = 'liability';
                $normalBalance = 'credit';
            } else {
                continue;
            }

            foreach ($accountType->accountGroups as $accountGroup) {
                $groupName = strtolower(trim($accountGroup->name));

                // Exclude Equity even if it is under Liabilities.
                if (str_contains($groupName, 'equity')) {
                    continue;
                }

                $ledgerBalances = [];
                $ledgerTotal = 0;

                foreach ($accountGroup->ledgers as $ledger) {
                    $balanceRow = $balances->get($ledger->id);

                    $debit = (float) ($balanceRow->total_debit ?? 0);
                    $credit = (float) ($balanceRow->total_credit ?? 0);

                    $balance = $normalBalance === 'debit'
                        ? $debit - $credit
                        : $credit - $debit;

                    $ledgerBalances[] = [
                        'ledger_id' => $ledger->id,
                        'ledger_name' => $ledger->name,
                        'balance' => round($balance, 2),
                    ];

                    $ledgerTotal += $balance;
                }

                $report[$section][] = [
                    'account_type_id' => $accountType->id,
                    'account_type_name' => $accountType->name,
                    'account_group_id' => $accountGroup->id,
                    'account_group_name' => $accountGroup->name,
                    'section' => $section,
                    'ledger_balances' => $ledgerBalances,
                    'ledger_total' => round($ledgerTotal, 2),
                ];
            }
        }

        return $report;
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate adjustment total
    |--------------------------------------------------------------------------
    */

    private function calculateAdjustmentTotal(array $adjustments): float
    {
        $total = 0;

        foreach ($adjustments as $adjustment) {
            $name = trim($adjustment['name'] ?? '');
            $amount = $adjustment['amount'] ?? null;

            if ($name !== '' && $amount !== null && $amount !== '') {
                $total += (float) $amount;
            }
        }

        return round($total, 2);
    }
}