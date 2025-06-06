<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => Inertia::render('Home'))->name('dashboard'); // alias aquí
});

Route::middleware('auth')->group(function () {
    // Vistas Inertia existentes
    Route::get('/', fn() => Inertia::render('Home'))->name('dashboard');
    Route::get('/products/create', fn() => Inertia::render('CreateProduct'))->name('products.create');

    // PRODUCTOS
    // Mostrar todos los productos
    Route::post('/products', [ProductController::class, 'store']);
    // Editar productos
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');;
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

    // Borrar producto
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    // Mostrar productos de cada usuario
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.mine');
    Route::get('/products', [ProductController::class, 'index']);

    // CARRITO
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{product_id}', [CartController::class, 'destroy']);

    // ÓRDENES
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/checkout', [OrderController::class, 'checkout']);

    // NOTIFICACIONES
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // CHAT
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{userId}', [ConversationController::class, 'show']);
    Route::post('/messages', [MessageController::class, 'store']);
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
