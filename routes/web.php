<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderPrintController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreparationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('giris', [LoginController::class, 'create'])->name('login');
    Route::post('giris', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('cikis', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('profilim', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profilim', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profilim/sifre', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('masalar', [DiningTableController::class, 'index'])->name('tables.index');

    Route::get('siparisler', [OrderController::class, 'index'])->name('orders.index');
    Route::get('siparisler/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('siparisler/{order}/adisyon', [OrderPrintController::class, 'receipt'])->whereNumber('order')->name('orders.print.receipt');
    Route::get('hazirlik', [PreparationController::class, 'index'])->name('preparation.index');

    // Sipariş açma ve düzenleme: garson ve yönetici
    Route::middleware('role:yonetici,garson')->group(function () {
        Route::post('siparisler', [OrderController::class, 'store'])->name('orders.store');
        Route::post('masalar/{table}/siparis', [OrderController::class, 'openForTable'])->name('tables.open-order');
        Route::patch('siparisler/{order}/not', [OrderController::class, 'updateNote'])->name('orders.note');
        Route::post('siparisler/{order}/iptal', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('siparisler/{order}/tasi', [OrderController::class, 'move'])->name('orders.move');
        Route::post('siparisler/{order}/birlestir', [OrderController::class, 'merge'])->name('orders.merge');
        Route::post('siparisler/{order}/kalemler', [OrderItemController::class, 'store'])->name('orders.items.store');
        Route::post('siparisler/{order}/kalemler/{item}/iptal', [OrderItemController::class, 'cancel'])->name('orders.items.cancel');
        Route::post('siparisler/{order}/durum', [OrderStatusController::class, 'advance'])->name('orders.advance');
    });

    // Kasa ve ödeme: kasiyer ve yönetici
    Route::middleware('role:yonetici,kasiyer')->group(function () {
        Route::post('siparisler/{order}/odemeler', [PaymentController::class, 'store'])->name('orders.payments.store');

        Route::get('kasa', [CashController::class, 'index'])->name('cash.index');
        Route::post('kasa/ac', [CashController::class, 'open'])->name('cash.open');
        Route::post('kasa/hareket', [CashController::class, 'movement'])->name('cash.movement');
        Route::post('kasa/kapat', [CashController::class, 'close'])->name('cash.close');
        Route::get('kasa/gecmis', [CashController::class, 'history'])->name('cash.history');
        Route::get('kasa/oturumlar/{session}', [CashController::class, 'show'])->name('cash.sessions.show');
    });

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

        Route::resource('calisanlar', UserController::class)
            ->except('show')
            ->parameters(['calisanlar' => 'user'])
            ->names('users');

        Route::get('islem-gecmisi', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('raporlar', [ReportController::class, 'index'])->name('reports.index');

        Route::get('stok-hareketleri', [StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::post('urunler/{product}/stok', [StockMovementController::class, 'store'])->name('products.stock');
    });
});
