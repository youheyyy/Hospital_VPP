<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Department routes (protected)
Route::middleware('auth')->group(function () {
    Route::get('/department/dashboard', function () {
        return view('department.dashboard');
    })->name('department.dashboard');

    Route::get('/department/request', function () {
        return view('department.request');
    })->name('department.request');

    // Admin dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Temporary buyer dashboard (to be implemented)
    Route::get('/buyer/dashboard', function () {
        return 'Buyer Dashboard - Coming Soon';
    })->name('buyer.dashboard');
});
