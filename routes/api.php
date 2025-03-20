<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MetaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{tag}', [TagController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Posts
        Route::post('posts', [PostController::class, 'store']);
        Route::put('posts/{post}', [PostController::class, 'update']);
        Route::delete('posts/{post}', [PostController::class, 'destroy']);
        Route::patch('posts/{post}/status', [PostController::class, 'updateStatus']);
        Route::patch('posts/{post}/visibility', [PostController::class, 'toggleVisibility']);

        // Categories
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        Route::post('categories/reorder', [CategoryController::class, 'reorder']);
        Route::patch('categories/{category}/move', [CategoryController::class, 'move']);

        // Tags
        Route::post('tags', [TagController::class, 'store']);
        Route::put('tags/{tag}', [TagController::class, 'update']);
        Route::delete('tags/{tag}', [TagController::class, 'destroy']);
        Route::post('tags/merge', [TagController::class, 'merge']);
        Route::post('tags/bulk-update', [TagController::class, 'bulkUpdate']);

        // Media
        Route::post('media', [MediaController::class, 'store']);
        Route::get('media', [MediaController::class, 'index']);
        Route::get('media/{media}', [MediaController::class, 'show']);
        Route::post('media/{media}', [MediaController::class, 'update']);
        Route::delete('media/{media}', [MediaController::class, 'destroy']);
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete']);

        // Meta
        Route::post('meta', [MetaController::class, 'store']);
        Route::get('meta', [MetaController::class, 'index']);
        Route::get('meta/{meta}', [MetaController::class, 'show']);
        Route::put('meta/{meta}', [MetaController::class, 'update']);
        Route::delete('meta/{meta}', [MetaController::class, 'destroy']);
        Route::get('meta/{meta}/analyze', [MetaController::class, 'analyze']);
        Route::post('meta/bulk-update', [MetaController::class, 'bulkUpdate']);
        Route::post('meta/generate', [MetaController::class, 'generateForModel']);
    });
});