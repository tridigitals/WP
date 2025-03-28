<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserController::class);
    Route::post('media/upload', [MediaController::class, 'store'])->name('media.store');
    
    // Role Management
    Route::resource('roles', RoleController::class);
    
    // Permission Management
    Route::resource('permissions', PermissionController::class);

    // Tag Management
    Route::resource('tags', TagController::class);
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);

    // Post Management
    Route::resource('posts', PostController::class);
    Route::controller(PostController::class)->prefix('posts')->name('posts.')->group(function () {
        Route::delete('{id}/force', 'forceDelete')->name('force-delete');
        Route::post('{id}/restore', 'restore')->name('restore');
    });
});