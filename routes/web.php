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

    Route::get('/dashboard', [
        App\Http\Controllers\DashboardController::class,
        'index'
    ])->name('dashboard');
});





//Route::get('/dashboard', [DashboardController::class, 'index']);



Route::resource('categories', CategoryController::class);
Route::resource('items', ItemController::class);
Route::resource('purchases', PurchaseController::class);
Route::resource('sales', SaleController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('customers', CustomerController::class);



Route::get(
    '/purchases/{purchase}/invoice',
    [PurchaseController::class, 'invoice']
)->name('purchases.invoice');

Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])
    ->name('sales.invoice');
Route::get('/export-sales/{sale}/invoice', [SaleController::class, 'invoiceExport'])
    ->name('export-sales.invoice');

Route::get('/stock-summary', [StockController::class, 'summary'])
    ->name('stock.summary');

Route::resource('purchase-inventories', PurchaseInventoryController::class);
Route::resource('expenses', ExpenseController::class);
Route::resource('packing-usages', PackingUsageController::class);
Route::resource('expense-categories', ExpenseCategoryController::class);
Route::get(
    '/inventory-summary',
    [PurchaseInventoryController::class, 'inventorySummary']
)->name('inventory.summary');

Route::get('/export-sales/create', [SaleController::class, 'createExport'])->name('export-sales.create');
Route::post('/export-sales/store', [SaleController::class, 'storeExport'])->name('export-sales.store');
Route::get('/export-sales/{id}/edit', [SaleController::class, 'editExport'])->name('export-sales.edit');
Route::put('/export-sales/{id}', [SaleController::class, 'updateExport'])
    ->name('export-sales.update');
Route::get('/export-sales', [SaleController::class, 'indexExport'])
    ->name('export-sales.index');
Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])
    ->name('customers.statement');

Route::post('/customers/payment', [CustomerController::class, 'storePayment'])
    ->name('customers.payment.store');

Route::get('/suppliers/{id}/statement', [SupplierController::class, 'statement'])
    ->name('suppliers.statement');

Route::post('/suppliers/{id}/payment', [SupplierController::class, 'storePayment'])
    ->name('suppliers.payment.store');
Route::post('/suppliers/{id}/inventory-payment', [SupplierController::class, 'storeInventoryPayment'])
    ->name('suppliers.inventory.payment.store');

Route::prefix('pos')->group(function () {

    Route::get('/local', [PosController::class, 'local'])->name('pos.local');

    Route::get('/export', [PosController::class, 'export'])->name('pos.export');
});

Route::resource('shipment-plans', ShipmentPlanController::class);
Route::resource('packings', PackingController::class);

use App\Http\Controllers\ReportController;

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

Route::resource('freight-records', FreightRecordController::class);
Route::resource('/landing-costs', LandingCostController::class);
