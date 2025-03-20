<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MetaController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'getQuickStats'])->name('stats');

    // Posts Management
    Route::resource('posts', PostController::class);
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::post('{post}/publish', [PostController::class, 'publish'])->name('publish');
        Route::post('{post}/unpublish', [PostController::class, 'unpublish'])->name('unpublish');
        Route::post('{post}/schedule', [PostController::class, 'schedule'])->name('schedule');
        Route::post('bulk-action', [PostController::class, 'bulkAction'])->name('bulk-action');
        Route::post('{post}/featured-image', [PostController::class, 'updateFeaturedImage'])->name('featured-image');
    });

    // Categories Management
    Route::resource('categories', CategoryController::class);
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('reorder', [CategoryController::class, 'reorder'])->name('reorder');
        Route::patch('{category}/move', [CategoryController::class, 'move'])->name('move');
    });

    // Tags Management
    Route::resource('tags', TagController::class);
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::post('merge', [TagController::class, 'merge'])->name('merge');
        Route::post('bulk-update', [TagController::class, 'bulkUpdate'])->name('bulk-update');
    });

    // Media Management
    Route::resource('media', MediaController::class);
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('browser', [MediaController::class, 'browser'])->name('browser');
        Route::post('upload', [MediaController::class, 'upload'])->name('upload');
        Route::post('bulk-delete', [MediaController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('{media}/regenerate', [MediaController::class, 'regenerate'])->name('regenerate');
        Route::post('optimize', [MediaController::class, 'optimize'])->name('optimize');
    });

    // SEO & Meta Management
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', [MetaController::class, 'index'])->name('index');
        Route::get('{type}/{id}', [MetaController::class, 'edit'])->name('edit');
        Route::put('{type}/{id}', [MetaController::class, 'update'])->name('update');
        Route::post('analyze', [MetaController::class, 'analyze'])->name('analyze');
        Route::post('bulk-update', [MetaController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('generate', [MetaController::class, 'generate'])->name('generate');
    });

    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::get('cache', [SettingsController::class, 'cache'])->name('cache');
        Route::post('cache/clear', [SettingsController::class, 'clearCache'])->name('cache.clear');
        Route::get('maintenance', [SettingsController::class, 'maintenance'])->name('maintenance');
        Route::post('maintenance/toggle', [SettingsController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    });
});