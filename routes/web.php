<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

// Rutas públicas (sin autenticación)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Token CSRF para requests AJAX (si lo necesitas)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    // Dashboard/Home
    Route::get('/', fn() => Inertia::render('Home'))->name('dashboard');
    
    // Gestión de productos
    Route::get('/products/create', fn() => Inertia::render('CreateProduct'))->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.mine');
    
    // Carrito de compras
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{product_id}', [CartController::class, 'destroy'])->name('cart.destroy');
    
    // Órdenes/Pedidos
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    
    // Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    
    // Chat/Conversaciones
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{userId}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});

// Rutas de autenticación (login, logout, register, etc.)
require __DIR__ . '/auth.php';

// Otras configuraciones
require __DIR__ . '/settings.php';