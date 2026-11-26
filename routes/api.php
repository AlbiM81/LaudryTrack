<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierAccountController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('can:access-owner')
        ->prefix('owner/cashiers')
        ->name('owner.cashiers.')
        ->controller(CashierAccountController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{cashier}', 'show')->name('show');
            Route::match(['put', 'patch'], '/{cashier}', 'update')->name('update');
            Route::delete('/{cashier}', 'destroy')->name('destroy');
        });

    Route::get('/owner/check', function () {
        Gate::authorize('access-owner');

        return response()->json(['message' => 'Akses Pemilik diterima']);
    });

    Route::get('/staff/check', function () {
        Gate::authorize('access-staff');

        return response()->json(['message' => 'Akses Kasir/Pemilik diterima']);
    });
});
