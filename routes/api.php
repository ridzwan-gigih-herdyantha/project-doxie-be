<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatSessionController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'me']);
    });

    Route::apiResource('documents', DocumentController::class)->only([
        'index', 'store', 'show', 'destroy',
    ]);

    Route::get('/sessions', [ChatSessionController::class, 'UserSession']);
    Route::get('documents/{document}/session', [ChatSessionController::class, 'index']);
    Route::post('documents/{document}/session', [ChatSessionController::class, 'store']);

    Route::get('session/{chatSession}/messages', [MessageController::class, 'index']);
    Route::post('session/{chatSession}/messages', [MessageController::class, 'store']);

});
