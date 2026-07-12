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

        $otherIncome = AccountGroup::firstOrCreate([
            'account_type_id' => $income->id,
            'name' => 'Other Income'
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
            'name' => 'Cash'
        ]);

        $bank = Ledger::firstOrCreate([
            'account_group_id' => $currentAssets->id,
            'name' => 'Bank Accounts'
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

        // FIXED ASSETS
        $vehicles = Ledger::firstOrCreate([
            'account_group_id' => $fixedAssets->id,
            'name' => 'Vehicles'
        ]);

        $machinery = Ledger::firstOrCreate([
            'account_group_id' => $fixedAssets->id,
            'name' => 'Machinery'
        ]);

        // LIABILITY
        $ap = Ledger::firstOrCreate([
            'account_group_id' => $currentLiab->id,
            'name' => 'Accounts Payable'
        ]);

        $salaryPayable = Ledger::firstOrCreate([
            'account_group_id' => $currentLiab->id,
            'name' => 'Salaries Payable'
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
            'account_group_id' => $operating->id,
            'name' => 'Salaries'
        ]);

        $electricity = Ledger::firstOrCreate([
            'account_group_id' => $operating->id,
            'name' => 'Electricity'
        ]);

        $fuel = Ledger::firstOrCreate([
            'account_group_id' => $operating->id,
            'name' => 'Fuel'
        ]);

        $rent = Ledger::firstOrCreate([
            'account_group_id' => $operating->id,
            'name' => 'Rent'
        ]);

        /*
        =========================
        SUB LEDGERS
        =========================
        */

        // CASH
        SubLedger::firstOrCreate(['ledger_id' => $cash->id, 'name' => 'Head Office Cash']);
        SubLedger::firstOrCreate(['ledger_id' => $cash->id, 'name' => 'Branch Cash']);

        // BANK
        SubLedger::firstOrCreate(['ledger_id' => $bank->id, 'name' => 'HNB Bank']);
        SubLedger::firstOrCreate(['ledger_id' => $bank->id, 'name' => 'Commercial Bank']);

        // RECEIVABLES (CUSTOMERS)
        SubLedger::firstOrCreate(['ledger_id' => $ar->id, 'name' => 'Customer A']);
        SubLedger::firstOrCreate(['ledger_id' => $ar->id, 'name' => 'Customer B']);

        // PAYABLES (SUPPLIERS)
        SubLedger::firstOrCreate(['ledger_id' => $ap->id, 'name' => 'Supplier A']);
        SubLedger::firstOrCreate(['ledger_id' => $ap->id, 'name' => 'Supplier B']);
    }
}