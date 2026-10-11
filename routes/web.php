<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::post('/orders/{order}/items', [OrderItemController::class, 'store'])->name('orders.items.store');
    Route::put('/orders/{order}/items/{product}', [OrderItemController::class, 'update'])->name('orders.items.update');
    Route::delete('/orders/{order}/items/{product}', [OrderItemController::class, 'destroy'])->name('orders.items.destroy');

    Route::post('/orders/{order}/submit', [OrderController::class, 'submit'])->name('orders.submit');
    Route::post('/orders/{order}/review', [OrderController::class, 'review'])->name('orders.review');
    Route::post('/orders/{order}/dispatch', [OrderController::class, 'dispatch'])->name('orders.dispatch');
});
