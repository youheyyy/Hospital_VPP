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
    })->name('department.request.create');

    Route::get('/department/list-request', function () {
        return view('department.list_request');
    })->name('department.list_request');

    Route::get('/department/request/{id}', function ($id) {
        return view('department.request', ['id' => $id]);
    })->name('department.request.show');

    Route::get('/department/request/{id}/edit', function ($id) {
        return view('department.request', ['id' => $id]);
    })->name('department.request.edit');

    // Admin dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Admin approve summary votes
    Route::get('/admin/approve-summary-votes', function () {
        return view('admin.approve_summary_votes');
    })->name('admin.approve_summary_votes');

    // Admin report
    Route::get('/admin/report', function () {
        return view('admin.report');
    })->name('admin.report');

    // Admin department management
    Route::get('/admin/department', function () {
        return view('admin.department');
    })->name('admin.department');

    // Admin product management
    Route::get('/admin/product', function () {
        return view('admin.product');
    })->name('admin.product');

    // Temporary buyer dashboard (to be implemented)
    Route::get('/buyer/dashboard', function () {
        return 'Buyer Dashboard - Coming Soon';
    })->name('buyer.dashboard');
});