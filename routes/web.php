<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FreightRecordController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LandingCostController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\PackingUsageController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesPaymentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PurchaseInventoryController;
use App\Http\Controllers\ShipmentPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\FreightController;
use App\Http\Controllers\FreightServiceController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\SubLedgerController;
use App\Http\Controllers\SalesProfitLossController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\SalaryAdvanceController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceCompanyProfileController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {


    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::resource(
        'invoice-company-profiles',
        InvoiceCompanyProfileController::class
    )->only(['index', 'create', 'store', 'edit', 'update']);

    Route::get(
        'cashbooks',
        [CashbookController::class, 'index']
    )->name('cashbooks.index');

    Route::get(
        'cashbooks/create',
        [CashbookController::class, 'create']
    )->name('cashbooks.create');

    Route::post(
        'cashbooks',
        [CashbookController::class, 'store']
    )->name('cashbooks.store');

    Route::get(
        'cashbooks/ledger/{ledger}/subledgers',
        [CashbookController::class, 'subLedgers']
    )->name('cashbooks.subledgers');

    Route::get(
        'items/parent-search',
        [ItemController::class, 'parentSearch']
    )->name('items.parent.search');

    Route::post(
        'items/update-parent',
        [ItemController::class, 'updateParent']
    )->name('items.parent.update');



    Route::resource('salary-advances', SalaryAdvanceController::class);

    Route::get('salary-advances/{salaryAdvance}/return', [SalaryAdvanceController::class, 'returnForm'])
        ->name('salary-advances.return');

    Route::post('salary-advances/{salaryAdvance}/return', [SalaryAdvanceController::class, 'storeReturn'])
        ->name('salary-advances.store-return');



    Route::get('reports/general-ledger', [GeneralLedgerController::class, 'index'])
        ->name('reports.general-ledger');

    Route::post('reports/general-ledger', [GeneralLedgerController::class, 'report'])
        ->name('reports.general-ledger.report');
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial_balance');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('items', ItemController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::post(
        '/purchases/{purchase}/amendment',
        [PurchaseController::class, 'storeAmendment']
    )->name('purchases.amendment.store');
    Route::resource('sales', SaleController::class);
    Route::get('suppliers/supplier-summary', [ReportController::class, 'supplierSummary'])->name('reports.supplier-summary');
    Route::resource('suppliers', SupplierController::class);
    Route::get('/customers/local', [CustomerController::class, 'local'])
    ->middleware('permission:customers.local')
    ->name('customers.local');
    Route::get('/customers/export', [CustomerController::class, 'export'])
    ->middleware('permission:customers.export')
    ->name('customers.export');
    Route::get('customers/customer-summary-local', [ReportController::class, 'customerSummaryLocal'])->name('reports.customer-summary-local');
    Route::get('customers/customer-summary-export', [ReportController::class, 'customerSummaryExport'])->name('reports.customer-summary-export');
    Route::resource('customers', CustomerController::class);

    Route::resource('fixed-assets', FixedAssetController::class);
    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])->name('purchases.invoice');
    Route::get('/purchases/{purchase}/invoice/pdf',  [PurchaseController::class, 'invoicePdf'])->name('purchases.invoice.pdf');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/sales/{id}/invoice-pdf', [SaleController::class, 'invoicePdf'])->name('sales.invoice.pdf');
    Route::get(
        '/sales/{id}/export-invoice-pdf',
        [SaleController::class, 'invoiceExportPdf']
    )->name('sales.export.invoice.pdf');
    Route::get('/export-sales/{sale}/invoice', [SaleController::class, 'invoiceExport'])->name('export-sales.invoice');
    Route::get('/stock-summary', [StockController::class, 'summary'])->name('stock.summary');
    Route::resource('purchase-inventories', PurchaseInventoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::patch('expenses/{id}/mark-paid', [ExpenseController::class, 'markPaid'])
        ->name('expenses.mark-paid');
    Route::resource('packing-usages', PackingUsageController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::get('/expense-categories/{id}/statement', [ExpenseCategoryController::class, 'statement'])->name('expense-categories.statement');
    Route::get('/inventory-summary', [PurchaseInventoryController::class, 'inventorySummary'])->name('inventory.summary');
    Route::get('/export-sales/create', [SaleController::class, 'createExport'])->name('export-sales.create');
    Route::post('/export-sales/store', [SaleController::class, 'storeExport'])->name('export-sales.store');
    Route::get('/export-sales/{id}/edit', [SaleController::class, 'editExport'])->name('export-sales.edit');
    Route::put('/export-sales/{id}', [SaleController::class, 'updateExport'])->name('export-sales.update');
    Route::delete('/export-sales/{id}', [SaleController::class, 'destroyExport'])->name('export-sales.destroy');
    Route::get('/export-sales', [SaleController::class, 'indexExport'])->name('export-sales.index');
    Route::get('/customers/{id}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::post('/customers/{id}/payment', [CustomerController::class, 'storePayment'])->name('customers.payment.store');
    Route::get('/suppliers/{id}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');
    Route::post('/suppliers/{id}/payment', [SupplierController::class, 'storePayment'])->name('suppliers.payment.store');
    Route::post('/suppliers/{id}/inventory-payment', [SupplierController::class, 'storeInventoryPayment'])->name('suppliers.inventory.payment.store');
    Route::post('/suppliers/{id}/asset-payment', [SupplierController::class, 'storeAssetPayment'])->name('suppliers.asset.payment.store');
    Route::post('/suppliers/{supplier}/opening-balance',[SupplierController::class, 'storeOpeningBalance'])->name('suppliers.opening-balance');
    Route::post('/customers/{customer}/opening-balance',[CustomerController::class, 'storeOpeningBalance'])->name('customers.opening-balance');

    Route::prefix('pos')->group(function () {

        Route::get('/local', [PosController::class, 'local'])->name('pos.local');
        Route::get('/export', [PosController::class, 'export'])->name('pos.export');
    });

    Route::resource('shipment-plans', ShipmentPlanController::class);
    Route::resource('packings', PackingController::class);
    Route::prefix('reports')->group(function () {

        Route::get('/item-profit-analysis', [ReportController::class, 'itemProfitAnalysis'])
            ->name('reports.item-profit-analysis');

        Route::get('/invoice-profit-analysis', [ReportController::class, 'invoiceProfitAnalysis'])
            ->name('reports.invoice-profit-analysis');

        Route::post('/invoice-profit-analysis-save', [ReportController::class, 'invoiceProfitAnalysisStore'])
            ->name('reports.invoice-profit-analysis-save');

        Route::get('/branch-comparison', [ReportController::class, 'indexBranchComparison'])
            ->name('reports.branch-comparison');

        Route::post('/branch-comparison', [ReportController::class, 'generateBranchComparison'])
            ->name('reports.branch-comparison.generate');
    });



    Route::prefix('sales-profit-loss')
        ->name('sales-profit-loss.')
        ->group(function () {

            Route::get('/', [
                SalesProfitLossController::class,
                'index'
            ])->name('index');

            Route::get('/create', [
                SalesProfitLossController::class,
                'create'
            ])->name('create');

            Route::post('/', [
                SalesProfitLossController::class,
                'store'
            ])->name('store');

            Route::get('/{sale}/edit', [
                SalesProfitLossController::class,
                'edit'
            ])->name('edit');

            Route::put('/{salesProfitLoss}', [
                SalesProfitLossController::class,
                'update'
            ])->name('update');
        });

    Route::resource('freight-records', FreightRecordController::class);
    Route::resource('/landing-costs', LandingCostController::class);

    Route::resource('freight-services', FreightServiceController::class)->except(['show', 'destroy']);
    Route::resource('freights', FreightController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('freights/{id}/payment', [FreightController::class, 'payment'])->name('freights.payment');
    Route::post('freights/{id}/cancel', [FreightController::class, 'cancel'])->name('freights.cancel');
});

Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Chart of Accounts
    |--------------------------------------------------------------------------
    */

    Route::get(
        'chart-of-accounts',
        [ChartOfAccountController::class, 'index']
    )->name('chart-of-accounts.index');


    /*
    |--------------------------------------------------------------------------
    | Account Groups
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'account-groups',
        AccountGroupController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | Ledgers
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'ledgers',
        LedgerController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | Sub Ledgers
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'sub-ledgers',
        SubLedgerController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */

    Route::get(
        'ledgers/by-account-group/{accountGroup}',
        [LedgerController::class, 'byAccountGroup']
    )->name('ledgers.by-account-group');
});

use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\RoleController;

Route::middleware(['auth'])
    ->prefix('accounting')
    ->name('accounting.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Journal Entries
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'journal-entries',
            JournalEntryController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Journal Entry AJAX
        |--------------------------------------------------------------------------
        */

        Route::get(
            'journal-entries/account-groups/{accountType}',
            [JournalEntryController::class, 'accountGroups']
        )->name('journal-entries.account-groups');

        Route::get(
            'journal-entries/ledgers/{accountGroup}',
            [JournalEntryController::class, 'ledgers']
        )->name('journal-entries.ledgers');

        Route::get(
            'journal-entries/sub-ledgers/{ledger}',
            [JournalEntryController::class, 'subLedgers']
        )->name('journal-entries.sub-ledgers');
    });

Route::middleware(['auth', 'head.office'])
    ->prefix('branches')
    ->name('branches.')
    ->group(function () {

        // Branch management
        Route::get('/', [BranchController::class, 'index'])
            ->name('index');

        Route::get('/create', [BranchController::class, 'create'])
            ->name('create');

        Route::post('/', [BranchController::class, 'store'])
            ->name('store');

        Route::get('/{branch}/edit', [BranchController::class, 'edit'])
            ->name('edit');

        Route::put('/{branch}', [BranchController::class, 'update'])
            ->name('update');

        Route::post('/{branch}/toggle-status', [
            BranchController::class,
            'toggleStatus'
        ])->name('toggle-status');


        // Branch users
        Route::get('/{branch}/users', [
            BranchController::class,
            'users'
        ])->name('users');

        Route::get('/{branch}/users/create', [
            BranchController::class,
            'createUser'
        ])->name('users.create');

        Route::post('/{branch}/users', [
            BranchController::class,
            'storeUser'
        ])->name('users.store');

        Route::get('/{branch}/users/{user}/edit', [
            BranchController::class,
            'editUser'
        ])->name('users.edit');

        Route::put('/{branch}/users/{user}', [
            BranchController::class,
            'updateUser'
        ])->name('users.update');

        Route::post('/{branch}/users/{user}/toggle-status', [
            BranchController::class,
            'toggleUserStatus'
        ])->name('users.toggle-status');

        Route::delete('/{branch}/users/{user}', [
            BranchController::class,
            'destroyUser'
        ])->name('users.destroy');
    });


Route::middleware(['auth', 'permission:roles.manage'])
    ->prefix('roles')
    ->name('roles.')
    ->controller(RoleController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/edit', 'edit')->name('edit');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatementOfAccountController;
use App\Models\StatementOfAccount;

Route::middleware(['auth'])
    ->prefix('settings')
    ->name('settings.')
    ->controller(SettingsController::class)
    ->group(function () {
        Route::get('/invoice-logo', 'invoiceLogo')
            ->name('invoice-logo');

        Route::post('/invoice-logo/{type}', 'updateInvoiceLogo')
            ->name('invoice-logo.update');
    });

Route::middleware(['auth', 'permission:accounting.chart-of-accounts'])
    ->prefix('account-setup')
    ->name('account-setup.')
    ->controller(ChartOfAccountController::class)
    ->group(function () {
        Route::get('/', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        Route::get('/account-types/{accountType}/groups', 'groups')
            ->name('groups');

        Route::get('/groups/{accountGroup}/ledgers', 'ledgers')
            ->name('ledgers');

        Route::get('/ledgers/{ledger}/subledgers', 'subLedgers')
            ->name('subledgers');
    });



Route::middleware(['auth'])
    ->prefix('statements')
    ->name('statements.')
    ->controller(StatementOfAccountController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');

        // Branch 2 creates drafts.
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        // Submit a Branch 2 draft to Branch 1.
        Route::post('/{statement}/submit', 'submit')
            ->name('submit');

        // View and edit, with authorization enforced in the controller.
        Route::get('/{statement}/edit', 'edit')->name('edit');
        Route::put('/{statement}', 'update')->name('update');
        Route::get('/{statement}', 'show')->name('show');
    });


use App\Http\Controllers\ProfitLossController;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/profit-loss', [
        ProfitLossController::class,
        'index'
    ])->name('reports.profit-loss');
});

