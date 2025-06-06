<?php

/* use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

// PRODUCTOS
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::get('/my-products', [ProductController::class, 'myProducts']);
});
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

//CHAT
Route::get('/conversations', [ConversationController::class, 'index']);
Route::get('/conversations/{userId}', [ConversationController::class, 'show']);
Route::post('/messages', [MessageController::class, 'store']);
 */
