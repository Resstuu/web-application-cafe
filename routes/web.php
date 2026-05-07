<?php

use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Production\ProductionController;
use App\Http\Controllers\Public\MenuOrderController;
use App\Http\Controllers\Public\MidtransNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuOrderController::class, 'index'])->name('public.order');
Route::post('/checkout', [MenuOrderController::class, 'checkout'])->name('public.checkout');
Route::post('/midtrans/notification', MidtransNotificationController::class)->name('midtrans.notification');
Route::get('/payment/{order}/finish', [MenuOrderController::class, 'finish'])->name('payment.finish');
Route::get('/payment/{order}/unfinish', [MenuOrderController::class, 'unfinish'])->name('payment.unfinish');
Route::get('/payment/{order}/error', [MenuOrderController::class, 'error'])->name('payment.error');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('menus', MenuController::class)->except(['show', 'create', 'edit']);
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware('role:kasir')->prefix('kasir')->name('cashier.')->group(function () {
        Route::get('/orders', [CashierOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [CashierOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [CashierOrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/paid', [CashierOrderController::class, 'markPaid'])->name('orders.paid');
        Route::patch('/orders/{order}/cancel', [CashierOrderController::class, 'cancel'])->name('orders.cancel');
    });

    Route::middleware('role:kitchen,barista')->group(function () {
        Route::get('/produksi/{category}', [ProductionController::class, 'index'])->name('production.index');
        Route::patch('/produksi/{order}/{category}/selesai', [ProductionController::class, 'complete'])->name('production.complete');
    });
});
