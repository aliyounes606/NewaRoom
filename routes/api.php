<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\V1\ArticleController as V1ArticleController;
use App\Http\Controllers\Api\V2\ArticleController as V2ArticleController;
use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;



// Authentication Routes 
Route::middleware(['api.log', 'throttle:api'])->prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    });
});

// Public Article Routes 
Route::middleware(['api.log', 'throttle:api'])->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
});

//  Protected Article Routes
Route::middleware(['auth:sanctum', 'api.log', 'throttle:api'])->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
});

//  Admin Dashboard Routes 
Route::middleware(['auth:sanctum', 'role:admin', 'api.log', 'throttle:api'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
});

//  Versioned API: V1 
Route::prefix('v1')->middleware(['api.log', 'throttle:api'])->group(function () {
    Route::get('/articles', [V1ArticleController::class, 'index']);
    Route::get('/articles/{article}', [V1ArticleController::class, 'show']);
});

//  Versioned API: V2 

Route::prefix('v2')->middleware(['api.log', 'throttle:api'])->group(function () {
    Route::get('/articles', [V2ArticleController::class, 'index']);
    Route::get('/articles/{article}', [V2ArticleController::class, 'show']);
});

