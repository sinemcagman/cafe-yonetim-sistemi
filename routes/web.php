<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('giris', [LoginController::class, 'create'])->name('login');
    Route::post('giris', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('cikis', [LoginController::class, 'destroy'])->name('logout');

    Route::view('/', 'dashboard')->name('dashboard');

    Route::get('masalar', [DiningTableController::class, 'index'])->name('tables.index');

    Route::middleware('role:yonetici')->group(function () {
        Route::resource('masalar', DiningTableController::class)
            ->except(['index', 'show'])
            ->parameters(['masalar' => 'table'])
            ->names('tables');

        Route::resource('kategoriler', CategoryController::class)
            ->except('show')
            ->parameters(['kategoriler' => 'category'])
            ->names('categories');

        Route::resource('urunler', ProductController::class)
            ->except('show')
            ->parameters(['urunler' => 'product'])
            ->names('products');

        Route::get('stok-hareketleri', [StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::post('urunler/{product}/stok', [StockMovementController::class, 'store'])->name('products.stock');
    });
});
