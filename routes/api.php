<?php

use App\Http\Controllers\ProductionController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('order', OrderController::class);
Route::apiResource('input', InputController::class);
Route::get('inputs/{input}/batches', [InputController::class, 'batches']);

Route::apiResource('recipe', RecipeController::class);

Route::prefix('productions')->group(function () {
    // Obtener historial de producciones (GET)
    Route::get('/', [ProductionController::class, 'index']);

    // Pre-calcular requerimientos (POST) - No afecta la base de datos
    Route::post('/calculate-requirements', [ProductionController::class, 'calculateRequirements']);

    // Ejecutar producción (POST) - Realiza cambios en la base de datos
    Route::post('/', [ProductionController::class, 'executeProduction']);

    Route::delete('/{id}', [ProductionController::class, 'destroy']);
});

Route::apiResource('product', ProductController::class);
Route::post('/products/link-production', [ProductController::class, 'linkProductionToProduct']);
Route::apiResource('user', UserController::class);
Route::apiResource('sale', SaleController::class);
