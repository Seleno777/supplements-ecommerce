<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\SubcategoriaController;
use App\Http\Controllers\Api\TallaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\ClienteController;

// Rutas públicas - lectura
Route::get('categorias', [CategoriaController::class, 'index']);
Route::get('categorias/{categoria}', [CategoriaController::class, 'show']);

Route::get('subcategorias', [SubcategoriaController::class, 'index']);
Route::get('subcategorias/{subcategoria}', [SubcategoriaController::class, 'show']);

Route::get('tallas', [TallaController::class, 'index']);
Route::get('tallas/{talla}', [TallaController::class, 'show']);

Route::get('productos', [ProductoController::class, 'index']);
Route::get('productos/{producto}', [ProductoController::class, 'show']);

Route::get('inventario', [InventarioController::class, 'index']);
Route::get('inventario/{inventario}', [InventarioController::class, 'show']);

Route::get('clientes', [ClienteController::class, 'index']);
Route::get('clientes/{cliente}', [ClienteController::class, 'show']);

// Rutas de autenticación pública
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas (autenticación necesaria)
Route::middleware('auth:sanctum')->group(function () {

    // Rutas de usuario autenticado
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });

    // Perfil cliente
    Route::get('clientes/profile', [ClienteController::class, 'profile']);
    Route::put('clientes/profile', [ClienteController::class, 'updateProfile']);
    Route::put('clientes/change-password', [ClienteController::class, 'updatePassword']);

    // Carrito
    Route::prefix('carrito')->group(function () {
        Route::get('/', [CarritoController::class, 'index']);
        Route::post('/', [CarritoController::class, 'store']);
        Route::put('{carrito}', [CarritoController::class, 'update']);
        Route::delete('{carrito}', [CarritoController::class, 'destroy']);
        Route::delete('/', [CarritoController::class, 'clear']);
        Route::get('/total', [CarritoController::class, 'total']);
    });

    // Rutas administrativas (CRUD completo) - solo admin
    Route::middleware('admin')->group(function () {

        Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
        Route::apiResource('subcategorias', SubcategoriaController::class)->except(['index', 'show']);
        Route::apiResource('tallas', TallaController::class)->except(['index', 'show']);
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('inventario', InventarioController::class)->except(['index', 'show']);

        Route::post('inventario/{inventario}/update-stock', [InventarioController::class, 'updateStock']);

        Route::apiResource('clientes', ClienteController::class)->except(['index', 'show']);
    });
});
