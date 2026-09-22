<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

Route::middleware(['auth:api', 'throttle:product-write'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
});
