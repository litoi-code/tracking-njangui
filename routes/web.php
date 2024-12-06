<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Account routes
Route::get('/accounts/{account}/transfers', [AccountController::class, 'transfers'])->name('accounts.transfers');
Route::resource('accounts', AccountController::class);
Route::get('/accounts/filter/{period}', [AccountController::class, 'filter'])->name('accounts.filter');

// Loan routes
Route::resource('loans', LoanController::class);
Route::post('loans/{loan}/make-payment', [LoanController::class, 'makePayment'])->name('loans.make-payment');
Route::get('loans/{loan}/amortization', [LoanController::class, 'calculateAmortization'])->name('loans.amortization');

// Loan payment routes
Route::get('/loans/{loan}/payments', [LoanPaymentController::class, 'index'])->name('loans.payments.index');
Route::post('/loans/{loan}/payments/{payment}/make', [LoanPaymentController::class, 'makePayment'])->name('loans.payments.make');

// Transfer routes
// Bulk transfer routes
Route::get('transfers/bulk', [TransferController::class, 'bulk'])->name('transfers.bulk');
Route::post('transfers/bulk', [TransferController::class, 'bulkStore'])->name('transfers.bulk.store');

// Distribution transfer routes
Route::get('transfers/distribute', [TransferController::class, 'distribute'])->name('transfers.distribute');
Route::post('transfers/distribute', [TransferController::class, 'distributeStore'])->name('transfers.distribute.store');

// Regular transfer routes
Route::get('transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
Route::resource('transfers', TransferController::class);
