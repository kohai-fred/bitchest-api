<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CryptoCotationController;
use App\Http\Controllers\CryptoCurrencyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'authenticate'])->name('login');

// ? Routes communes (admin & client)
Route::middleware('auth:sanctum')->group(
    function () {
        Route::get('/current-user', [UserController::class, 'getCurrentUser'])->name('current-user');
        Route::post('/check-email', [UserController::class, 'checkEmail'])->name('check-email');

        // Route::get('/cotations', [CryptoCotationController::class, 'all'])->name('cotations');
        Route::get('/latest-cotations', [CryptoCotationController::class, 'latestCotation'])->name('latest-cotations');
        Route::get('/crypto/{id}', [CryptoCotationController::class, 'cryptoCotationDetails'])->name('crypto-cotation-details');
        Route::get('/crypto-currencies', [CryptoCurrencyController::class, 'index'])->name('index');
    }
);

// ? Routes Admin
Route::prefix('admin')->middleware('auth:sanctum')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
});

// ? Routes Client
Route::prefix('client')->middleware('auth:sanctum')->name('client.')->group(function () {
    Route::get('/wallet/balance', [WalletController::class, 'getBalance'])->name('getBalance');
    Route::get('/transactions-history', [TransactionController::class, 'getAllUserTransactions'])->name('getAllUserTransactions');
    Route::post('/buy-crypto', [TransactionController::class, 'buyCrypto'])->name('buyCrypto');
    Route::post('/sell-crypto', [TransactionController::class, 'sellCrypto'])->name('sellCrypto');
    // Route::get('/cryptos-id-transactions', [TransactionController::class, 'getCryptoIdsOfUserTransactions'])->name('getCryptoIdsOfUserTransactions');
    Route::get('/crypto-remaining-quantity/{cryptoId}', [TransactionController::class, 'getRemainingQuantityOfCrypto'])->name('getRemainingQuantityOfCrypto');
    Route::get('/owned-crypto', [TransactionController::class, 'getUserOwnedCryptoData'])->name('getUserOwnedCryptoData');
    // TODO: faire une résumé des transactions
    Route::get(
        '/transactions-summary',
        [TransactionController::class, 'getTransactionSummary']
    )->name('getTransactionSummary');
});
