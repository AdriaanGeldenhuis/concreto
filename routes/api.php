<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\DriverApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Customer API
    Route::middleware('role:customer')->prefix('orders')->group(function () {
        Route::get('/', [CustomerApiController::class, 'listOrders']);
        Route::post('/', [CustomerApiController::class, 'createOrder']);
        Route::get('/{order}', [CustomerApiController::class, 'showOrder']);
        Route::post('/{order}/pay/yoco', [CustomerApiController::class, 'createPaymentSession']);
    });

    Route::middleware('role:customer')->group(function () {
        Route::get('/invoices', [CustomerApiController::class, 'listInvoices']);
        Route::get('/invoices/{invoice}/download', [CustomerApiController::class, 'downloadInvoice']);
    });

    // Driver API
    Route::middleware('role:driver')->prefix('driver')->group(function () {
        Route::get('/orders', [DriverApiController::class, 'listOrders']);
        Route::post('/orders/{order}/accept', [DriverApiController::class, 'acceptOrder']);
        Route::post('/orders/{order}/loaded', [DriverApiController::class, 'loadedOrder']);
        Route::post('/orders/{order}/transit', [DriverApiController::class, 'transitOrder']);
        Route::post('/orders/{order}/arrived', [DriverApiController::class, 'arrivedOrder']);
        Route::post('/orders/{order}/location', [DriverApiController::class, 'updateLocation']);
        Route::post('/orders/{order}/signature', [DriverApiController::class, 'storeSignature']);
    });

    // Admin API
    Route::middleware('role:admin,staff')->prefix('admin')->group(function () {
        Route::get('/orders', [AdminApiController::class, 'listOrders']);
        Route::post('/orders/{order}/assign-driver', [AdminApiController::class, 'assignDriver']);
        Route::get('/settings', [AdminApiController::class, 'getSettings']);
        Route::post('/settings', [AdminApiController::class, 'updateSettings']);
    });
});
