<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
     * Rutas especificas para el usuario Administrador
     */
    Route::middleware(['is_admin'])->group(function () {
        Route::apiResource('order', OrderController::class);
        Route::apiResource('input', InputController::class);
        Route::get('input/{input}/batches', [InputController::class, 'batches']);
        Route::apiResource('recipe', RecipeController::class);
        Route::apiResource('product', ProductController::class);
        Route::post('/products/link-production', [ProductController::class, 'linkProductionToProduct']);
        Route::apiResource('user', UserController::class);
        Route::apiResource('sale', SaleController::class)->except(['destroy']);
        Route::prefix('production')->group(function () {
            // Obtener historial de producciones (GET)
            Route::get('/', [ProductionController::class, 'index']);

            // Pre-calcular requerimientos (POST) - No afecta la base de datos
            Route::post('/calculate-requirements', [ProductionController::class, 'calculateRequirements']);

            // Obtener el detalle de Una produccion
            Route::get('/{id}', [ProductionController::class, 'show']);

            // Ejecutar producción (POST) - Realiza cambios en la base de datos
            Route::post('/', [ProductionController::class, 'executeProduction']);

            Route::delete('/{id}', [ProductionController::class, 'destroy']);
        });
    });

    /**
     * Rutas especificas para el usuario Cajero
     */
    Route::middleware(['is_cashier'])->group(function () {
        Route::apiResource('order', OrderController::class)->only(['index', 'show', 'store']);
        Route::apiResource('input', InputController::class)->only(['index', 'show']);
        Route::apiResource('product', ProductController::class)->only(['index', 'show']);
        Route::apiResource('sale', SaleController::class)->except(['index', 'show', 'store']);
    });

    /**
     * Rutas para el usuario panadero
     */
    Route::middleware(['is_baker'])->group(function () {
        Route::apiResource('product', ProductController::class)->only(['index', 'show', 'store']);
        Route::apiResource('input', InputController::class)->only(['index', 'show', 'store', 'update']);
        Route::apiResource('recipe', RecipeController::class)->only(['index', 'show', 'store', 'update']);
        Route::prefix('production')->group(function () {
            // Obtener historial de producciones (GET)
            Route::get('/', [ProductionController::class, 'index']);

            // Pre-calcular requerimientos (POST) - No afecta la base de datos
            Route::post('/calculate-requirements', [ProductionController::class, 'calculateRequirements']);

            // Ejecutar producción (POST) - Realiza cambios en la base de datos
            Route::post('/', [ProductionController::class, 'executeProduction']);

            Route::delete('/{id}', [ProductionController::class, 'destroy']);
        });
    });

    // Rutas comunes para todos los autenticados
    Route::apiResource('product', ProductController::class)->only(['index', 'show']);
});

// Route::apiResource('order', OrderController::class);
// Route::apiResource('input', InputController::class);
// Route::get('inputs/{input}/batches', [InputController::class, 'batches']);

// Route::apiResource('recipe', RecipeController::class);

// Route::prefix('productions')->group(function () {
//     // Obtener historial de producciones (GET)
//     Route::get('/', [ProductionController::class, 'index']);

//     // Pre-calcular requerimientos (POST) - No afecta la base de datos
//     Route::post('/calculate-requirements', [ProductionController::class, 'calculateRequirements']);

//     // Ejecutar producción (POST) - Realiza cambios en la base de datos
//     Route::post('/', [ProductionController::class, 'executeProduction']);

//     Route::delete('/{id}', [ProductionController::class, 'destroy']);
// });

// Route::apiResource('product', ProductController::class);
// Route::post('/products/link-production', [ProductController::class, 'linkProductionToProduct']);
// Route::apiResource('user', UserController::class);
// Route::apiResource('sale', SaleController::class);
