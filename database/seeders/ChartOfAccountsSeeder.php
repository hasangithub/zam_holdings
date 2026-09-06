<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Ledger;
use App\Models\SubLedger;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        /*
        =========================
        ACCOUNT TYPES (ORDERED)
        =========================
        */
        $assets     = AccountType::firstOrCreate(['name' => 'Assets']);
        $expenses   = AccountType::firstOrCreate(['name' => 'Expenses']);
        $income     = AccountType::firstOrCreate(['name' => 'Income']);
        $liability  = AccountType::firstOrCreate(['name' => 'Liability']);

        /*
        =========================
        ACCOUNT GROUPS
        =========================
        */

        // ASSETS
        $currentAssets = AccountGroup::firstOrCreate([
            'account_type_id' => $assets->id,
            'name' => 'Current Assets'
        ]);

        $fixedAssets = AccountGroup::firstOrCreate([
            'account_type_id' => $assets->id,
            'name' => 'Fixed Assets'
        ]);

        // LIABILITY
        $currentLiab = AccountGroup::firstOrCreate([
            'account_type_id' => $liability->id,
            'name' => 'Current Liabilities'
        ]);

        // INCOME
        $sales = AccountGroup::firstOrCreate([
            'account_type_id' => $income->id,
            'name' => 'Sales'
        ]);

        // EXPENSES
        $cogs = AccountGroup::firstOrCreate([
            'account_type_id' => $expenses->id,
            'name' => 'Cost of Goods Sold'
        ]);

        $operating = AccountGroup::firstOrCreate([
            'account_type_id' => $expenses->id,
            'name' => 'Operating Expenses'
        ]);

        /*
        =========================
        LEDGERS
        =========================
        */

        // CASH & BANK
        $cash = Ledger::firstOrCreate([
            'account_group_id' => $currentAssets->id,
            'name' => 'Cash and Bank Account'
        ]);

        $bank = Ledger::firstOrCreate([
            'account_group_id' => $currentAssets->id,
            'name' => 'Bank'
        ]);

        // RECEIVABLES
        $ar = Ledger::firstOrCreate([
            'account_group_id' => $currentAssets->id,
            'name' => 'Accounts Receivable'
        ]);

        // INVENTORY
        $inventory = Ledger::firstOrCreate([
            'account_group_id' => $currentAssets->id,
            'name' => 'Inventory'
        ]);

        // LIABILITY
        $ap = Ledger::firstOrCreate([
            'account_group_id' => $currentLiab->id,
            'name' => 'Accounts Payable'
        ]);

        // INCOME
        $localSales = Ledger::firstOrCreate([
            'account_group_id' => $sales->id,
            'name' => 'Local Sales'
        ]);

        $exportSales = Ledger::firstOrCreate([
            'account_group_id' => $sales->id,
            'name' => 'Export Sales'
        ]);

        // EXPENSES
        $salaryExpense = Ledger::firstOrCreate([
            'account_group_id' => $cogs->id,
            'name' => 'Local'
        ]);

        $electricity = Ledger::firstOrCreate([
            'account_group_id' => $cogs->id,
            'name' => 'Export'
        ]);

        $fuel = Ledger::firstOrCreate([
            'account_group_id' => $cogs->id,
            'name' => 'Freight Charges'
        ]);

        $rent = Ledger::firstOrCreate([
            'account_group_id' => $cogs->id,
            'name' => ' Packing Material'
        ]);

    SubLedger::firstOrCreate([
            'ledger_id' => $inventory->id,
            'name' => 'Trading Goods Inventory'
        ]);

  SubLedger::firstOrCreate([
            'ledger_id' => $inventory->id,
            'name' => 'Packing Material Inventory'
        ]);
    }
}
