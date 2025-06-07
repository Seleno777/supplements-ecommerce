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

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => Inertia::render('Home'))->name('dashboard');

    // Resto de tus rutas protegidas
    Route::get('/products/create', fn() => Inertia::render('CreateProduct'))->name('products.create');
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.mine');
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{product_id}', [CartController::class, 'destroy']);
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{userId}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/messages', [MessageController::class, 'store'])->name('chat.message');
});

// Ruta login
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/');
    }

    return response()->json(['message' => 'Credenciales inválidas.'], 422);
});

// Ruta logout 
Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
