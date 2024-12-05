<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Account routes
Route::resource('accounts', AccountController::class);
Route::get('/accounts/filter/{period}', [AccountController::class, 'filter'])->name('accounts.filter');

// Transfer routes
// Bulk transfer routes
Route::get('transfers/bulk', [TransferController::class, 'bulk'])->name('transfers.bulk');
Route::post('transfers/bulk', [TransferController::class, 'bulkStore'])->name('transfers.bulk.store');

// Distribution transfer routes
Route::get('transfers/distribute', [TransferController::class, 'distribute'])->name('transfers.distribute');
Route::post('transfers/distribute', [TransferController::class, 'distributeStore'])->name('transfers.distribute.store');

// Regular transfer routes
Route::resource('transfers', TransferController::class);
