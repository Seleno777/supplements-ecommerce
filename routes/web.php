<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => Inertia::render('Home'))->name('dashboard'); // alias aquí
    Route::get('/products/create', fn() => Inertia::render('CreateProduct'))->name('products.create');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
