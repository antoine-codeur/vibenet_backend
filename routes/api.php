<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\BlogController;
use App\Http\Controllers\API\V1\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
    // BlogController index (non-Authenticated)
    Route::get('explore', [BlogController::class, 'index']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // ProfileController routes
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::put('profile', [ProfileController::class, 'updateProfile']);
        Route::delete('profile', [ProfileController::class, 'deleteProfile']);

        // BlogController routes
        Route::post('blogs', [BlogController::class, 'store']);
        Route::get('blogs/{id}', [BlogController::class, 'show']);
        Route::put('blogs/{id}', [BlogController::class, 'update']);
        Route::delete('blogs/{id}', [BlogController::class, 'destroy']);
    });
});
