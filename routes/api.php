<?php

use App\Http\Controllers\Api\AdminMenuController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashierOrderController;
use App\Http\Controllers\Api\CustomerCheckoutController;
use App\Http\Controllers\Api\OrderLookupController;
use App\Http\Controllers\Api\ProductionOrderController;
use App\Http\Controllers\Api\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/menus', [PublicMenuController::class, 'index']);
Route::post('/customer/checkout', [CustomerCheckoutController::class, 'store']);
Route::get('/orders/{code}', [OrderLookupController::class, 'show']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::apiResource('menus', AdminMenuController::class)->except(['show']);
        Route::apiResource('users', AdminUserController::class)->except(['show']);
    });

    Route::middleware('role:kasir')->prefix('cashier')->group(function () {
        Route::get('/orders', [CashierOrderController::class, 'index']);
        Route::post('/orders', [CashierOrderController::class, 'store']);
        Route::patch('/orders/{order}/paid', [CashierOrderController::class, 'markPaid']);
        Route::patch('/orders/{order}/cancel', [CashierOrderController::class, 'cancel']);
    });

    Route::middleware('role:kitchen,barista')->group(function () {
        Route::get('/production/{category}', [ProductionOrderController::class, 'index']);
        Route::patch('/production/{order}/{category}/complete', [ProductionOrderController::class, 'complete']);
    });
});
