<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WalletController;

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
     * Actualizar perfil (nombre y apellido)
     */
    Route::put('/auth/me', [AuthController::class, 'updateProfile']);

    /**
     * Cerrar sesión
     */
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    /**
     * Pase de Apple Wallet de la tarjeta digital
     */
    Route::get('/wallet/apple-pass', [WalletController::class, 'applePass']);

    /**
     * Pase de Google Wallet de la tarjeta digital
     */
    Route::get('/wallet/google-pass', [WalletController::class, 'googlePass']);

});