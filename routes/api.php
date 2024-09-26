<?php

use App\Http\Controllers\API\V1\AdminController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\BlogController;
use App\Http\Controllers\API\V1\CommentController;
use App\Http\Controllers\API\V1\PostController;
use App\Http\Controllers\API\V1\ProfileController;
use App\Http\Controllers\API\V1\SubscriptionController;
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

        // AdminController routes
        Route::prefix('admin')->middleware(['admin'])->group(function () {
            Route::get('blogs', [AdminController::class, 'listBlogs']);
            Route::delete('blogs/{id}', [AdminController::class, 'destroyBlog']); // Delete blog
            Route::get('posts', [AdminController::class, 'listPosts']);
            Route::delete('posts/{id}', [AdminController::class, 'destroyPost']); // Delete post
            Route::get('comments', [AdminController::class, 'listComments']);
            Route::delete('comments/{id}', [AdminController::class, 'destroyComment']); // Delete comment
            Route::get('users', [AdminController::class, 'listUsers']);
            Route::delete('users/{id}', [AdminController::class, 'destroyUser']); // Delete user
            Route::get('uploads', [AdminController::class, 'listUploads']);
            Route::delete('uploads/{filename}', [AdminController::class, 'deleteUpload']); // Delete upload
        });

        // ProfileController routes
        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']);
        Route::delete('profile', [ProfileController::class, 'delete']);

        // BlogController routes
        Route::post('blogs', [BlogController::class, 'store']);
        Route::get('blogs/{id}', [BlogController::class, 'show']);
        Route::post('blogs/{id}', [BlogController::class, 'update']);
        Route::delete('blogs/{id}', [BlogController::class, 'destroy']);

        // SubscriptionController routes
        Route::get('subscriptions', [SubscriptionController::class, 'index']);
        Route::post('subscriptions/{blogId}', [SubscriptionController::class, 'subscribe']);
        Route::delete('subscriptions/{blogId}', [SubscriptionController::class, 'unsubscribe']);

        // PostController routes
        Route::post('blogs/{blogId}/posts', [PostController::class, 'store']);
        Route::get('blogs/{blogId}/posts', [PostController::class, 'index']);
        Route::get('posts/{id}', [PostController::class, 'show']);
        Route::post('posts/{id}/update', [PostController::class, 'update']);
        Route::delete('posts/{id}', [PostController::class, 'destroy']);

        // CommentController routes
        Route::post('posts/{postId}/comments', [CommentController::class, 'store']);
        Route::get('posts/{postId}/comments', [CommentController::class, 'index']);
        Route::delete('comments/{id}', [CommentController::class, 'destroy']);
        Route::put('comments/{id}/toggle', [CommentController::class, 'toggleVisibility']);
    });
});