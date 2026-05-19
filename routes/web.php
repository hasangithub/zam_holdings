<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesPaymentController;

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

require __DIR__.'/auth.php';

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

Route::post(
    '/purchase/{purchase}/payment',
    [PurchasePaymentController::class, 'store']
)->name('purchase.payment.store');


Route::post('/sales/{sale}/payment', [SalesPaymentController::class, 'store'])
    ->name('sales.payment.store');

Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])
    ->name('sales.invoice');