<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});


Route::get('/product/{id}', function (int $id): Response {
    return Inertia::render('ProductDetail', [
        'productId' => $id,
        'homeUrl' => route('home'),
    ]);
})->whereNumber('id')->name('products.page');

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])
        ->name('products.index');
    Route::get('/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])
        ->name('products.show');
});

require __DIR__ . '/settings.php';
