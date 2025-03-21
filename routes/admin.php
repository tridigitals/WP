<?php

use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Posts
    Route::resource('posts', PostController::class);
    Route::post('posts/{post}/toggle-status', [PostController::class, 'toggleStatus'])
        ->name('posts.toggle-status');
        
    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Tags
    Route::resource('tags', TagController::class);
    Route::get('tags-suggestions', [TagController::class, 'suggestions'])->name('tags.suggestions');
});