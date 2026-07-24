<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/login/verify', [AuthController::class, 'verifyLoginOtp']);

/*
|--------------------------------------------------------------------------
| Productos
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function () {

    /**
     * Productos
     */
    Route::get('/', [ProductController::class, 'index']);

    /**
     * Buscar productos
     */
    Route::get('/search', [ProductController::class, 'search']);

    /**
     * Departamentos
     */
    Route::get('/departments', [ProductController::class, 'departments']);

    /**
     * Limpiar caché del catálogo
     */
    Route::post('/cache/clear', [ProductController::class, 'clearCache']);

    /**
     * Detalle del producto
     *
     * SIEMPRE AL FINAL
     */
    Route::get('/{itemId}', [ProductController::class, 'show']);

});

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * Usuario autenticado
     */
    Route::get('/auth/me', [AuthController::class, 'me']);

    /**
     * Cerrar sesión
     */
    Route::post('/auth/logout', [AuthController::class, 'logout']);

});