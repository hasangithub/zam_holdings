<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard',

            'customers.local',
            'customers.export',
            'reports.customer-summary-local',
            'reports.customer-summary-export',

            'suppliers',
            'reports.supplier-summary',

            'purchases',
            'stock-summary',

            'sales.local',
            'sales.export',

            'inventory.purchase',
            'inventory.summary',
            'packing-usages',

            'expenses',
            'expense-categories',

            'freight-services',
            'freights',
            'salary-advances',
            'cashbooks',
            'fixed-assets',

            'reports.sales-profit-loss-local',
            'reports.invoice-profit-analysis',
            'shipment-plans',
            'packings',
            'reports.trial-balance',
            'reports.item-profit-analysis',
            'reports.balance-sheet',
            'reports.general-ledger',
            'freight-records',
            'landing-costs',
            'reports.branch-comparison',

            'accounting.chart-of-accounts',
            'accounting.account-groups',
            'accounting.ledgers',
            'accounting.sub-ledgers',
            'accounting.journal-entries',

            'categories',
            'items',
            'users',
            'branches.manage',
            'roles.manage',
            'users.roles.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', $permissions)
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
