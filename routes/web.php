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
use App\Http\Controllers\FreightController;
use App\Http\Controllers\FreightServiceController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\SubLedgerController;
use App\Http\Controllers\SalesProfitLossController;

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

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('items', ItemController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])->name('purchases.invoice');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/export-sales/{sale}/invoice', [SaleController::class, 'invoiceExport'])->name('export-sales.invoice');
    Route::get('/stock-summary', [StockController::class, 'summary'])->name('stock.summary');
    Route::resource('purchase-inventories', PurchaseInventoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::patch('expenses/{id}/mark-paid', [ExpenseController::class, 'markPaid'])
        ->name('expenses.mark-paid');
    Route::resource('packing-usages', PackingUsageController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::get('/inventory-summary', [PurchaseInventoryController::class, 'inventorySummary'])->name('inventory.summary');
    Route::get('/export-sales/create', [SaleController::class, 'createExport'])->name('export-sales.create');
    Route::post('/export-sales/store', [SaleController::class, 'storeExport'])->name('export-sales.store');
    Route::get('/export-sales/{id}/edit', [SaleController::class, 'editExport'])->name('export-sales.edit');
    Route::put('/export-sales/{id}', [SaleController::class, 'updateExport'])->name('export-sales.update');
    Route::delete('/export-sales/{id}', [SaleController::class, 'destroyExport'])->name('export-sales.destroy');
    Route::get('/export-sales', [SaleController::class, 'indexExport'])->name('export-sales.index');
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::post('/customers/{id}/payment', [CustomerController::class, 'storePayment'])->name('customers.payment.store');
    Route::get('/suppliers/{id}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');
    Route::post('/suppliers/{id}/payment', [SupplierController::class, 'storePayment'])->name('suppliers.payment.store');
    Route::post('/suppliers/{id}/inventory-payment', [SupplierController::class, 'storeInventoryPayment'])->name('suppliers.inventory.payment.store');
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

        Route::get('/customer-summary', [ReportController::class, 'customerSummary'])
            ->name('reports.customer-summary');
        Route::get('/supplier-summary', [ReportController::class, 'supplierSummary'])->name('reports.supplier-summary');
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
        )->except(['show']);


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
