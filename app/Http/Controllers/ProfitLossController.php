<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\JournalEntryDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = (int) auth()->user()->branch_id;

        $selectedBranch = request()->input('branch_scope', 'all');

        // Branch 1 can select all branches or only its own branch.
        // Other branches are always restricted to their own branch.
        if ($branchId === 1) {
            $showAllBranches = $selectedBranch === 'all';
        } else {
            $showAllBranches = false;
        }

        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date'   => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $fromDate = $validated['from_date']
            ?? now()->startOfMonth()->toDateString();

        $toDate = $validated['to_date']
            ?? now()->toDateString();

        // Branch 1 sees all branches.
        // Other branches see only their own branch.
        $branchQuery = function ($query) use ($branchId, $showAllBranches) {
            if (!$showAllBranches) {
                $query->where('journal_entries.branch_id', $branchId);
            }

            return $query;
        };

        /*
         * Get ledger balances for the selected period.
         * The report uses journal entries from all branches
         * for Head Office (branch_id = 1).
         */
        $balanceQuery = JournalEntryDetail::query()
            ->join(
                'journal_entries',
                'journal_entries.id',
                '=',
                'journal_entry_details.journal_entry_id'
            )
            ->select(
                'journal_entry_details.ledger_id',
                DB::raw('COALESCE(SUM(journal_entry_details.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_entry_details.credit), 0) as total_credit')
            )
            ->whereDate('journal_entries.journal_date', '>=', $fromDate)
            ->whereDate('journal_entries.journal_date', '<=', $toDate);

        $branchQuery($balanceQuery);

        $ledgerBalances = $balanceQuery
            ->groupBy('journal_entry_details.ledger_id')
            ->get()
            ->keyBy('ledger_id');

        // Load account types, groups and ledgers.
        $accountTypes = AccountType::with([
            'accountGroups.ledgers'
        ])->get();

        $incomeType = $accountTypes->first(function ($type) {
            return strtolower(trim($type->name)) === 'income';
        });

        $expenseType = $accountTypes->first(function ($type) {
            return in_array(
                strtolower(trim($type->name)),
                ['expense', 'expenses']
            );
        });

        abort_unless(
            $incomeType && $expenseType,
            500,
            'Income or Expenses account type was not found.'
        );

        $buildGroup = function ($group, $normalBalance) use ($ledgerBalances) {
            $ledgers = collect();
            $groupTotal = 0;

            foreach ($group->ledgers as $ledger) {
                $balance = $ledgerBalances->get($ledger->id);

                $debit = (float) ($balance->total_debit ?? 0);
                $credit = (float) ($balance->total_credit ?? 0);

                $amount = $normalBalance === 'credit'
                    ? $credit - $debit
                    : $debit - $credit;

                // Keep negative balances visible; they may indicate
                // a reversal or an unusual account balance.
                $ledgers->push([
                    'id'     => $ledger->id,
                    'name'   => $ledger->name,
                    'debit'  => $debit,
                    'credit' => $credit,
                    'amount' => $amount,
                ]);

                $groupTotal += $amount;
            }

            return [
                'id'     => $group->id,
                'name'   => $group->name,
                'ledgers' => $ledgers,
                'total'  => $groupTotal,
            ];
        };

        $salesGroups = collect();
        $cogsGroups = collect();
        $otherIncomeGroups = collect();
        $expenseGroups = collect();

        // Income: Sales and Other Income.
        foreach ($incomeType->accountGroups as $group) {
            $groupName = strtolower(trim($group->name));

            $data = $buildGroup($group, 'credit');

            if (str_contains($groupName, 'sales')) {
                $salesGroups->push($data);
            } else {
                $otherIncomeGroups->push($data);
            }
        }

        // Expenses: COGS and all other expense groups.
        foreach ($expenseType->accountGroups as $group) {
            $groupName = strtolower(trim($group->name));

            $data = $buildGroup($group, 'debit');

            if (
                str_contains($groupName, 'cost of goods sold')
                || str_contains($groupName, 'cogs')
            ) {
                $cogsGroups->push($data);
            } else {
                $expenseGroups->push($data);
            }
        }

        $salesTotal = $salesGroups->sum('total');
        $cogsTotal = $cogsGroups->sum('total');
        $grossProfit = $salesTotal - $cogsTotal;

        $otherIncomeTotal = $otherIncomeGroups->sum('total');
        $expensesTotal = $expenseGroups->sum('total');

        $netProfit = $grossProfit
            + $otherIncomeTotal
            - $expensesTotal;

        return view('reports.profit_loss', compact(
            'fromDate',
            'toDate',
            'salesGroups',
            'cogsGroups',
            'otherIncomeGroups',
            'expenseGroups',
            'salesTotal',
            'cogsTotal',
            'grossProfit',
            'otherIncomeTotal',
            'expensesTotal',
            'netProfit'
        ));
    }
}
