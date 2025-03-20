<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LogoutController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->group(base_path('routes/admin.php'));

// Redirect root to admin dashboard for authenticated users
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});
