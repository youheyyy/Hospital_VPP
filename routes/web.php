<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Department\DepartmentController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Department Code
Route::group(['prefix' => 'department', 'as' => 'department.', 'middleware' => ['auth', 'role:DEPARTMENT']], function () {
    Route::get('/dashboard', [DepartmentController::class, 'dashboard'])->name('dashboard');

    Route::get('/request/create', [DepartmentController::class, 'createrequest'])->name('request.create');
    Route::post('/request/store', [DepartmentController::class, 'store'])->name('request.store');

    Route::get('/products/search', [DepartmentController::class, 'searchProducts'])->name('products.search');

    // Additional placeholder routes if views exist
    Route::get('/list-request', [DepartmentController::class, 'listRequests'])->name('list_request');
    Route::get('/request/{id}', function ($id) {
        return view('department.request_detail', ['id' => $id]);
    })->name('request.show');
});

// Admin Code
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:ADMIN']], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Requests
    Route::get('/requests', [AdminController::class, 'indexRequests'])->name('requests.index');
    Route::post('/requests/{id}/approve', [AdminController::class, 'approveRequest'])->name('requests.approve');

    // Aggregation
    Route::get('/aggregation', [AdminController::class, 'indexAggregation'])->name('aggregation.index');
    Route::post('/aggregation/process', [AdminController::class, 'processAggregation'])->name('aggregation.process');
    Route::post('/aggregation/approve-dept/{id}', [AdminController::class, 'approveDepartment'])->name('aggregation.approve_dept');

    // Orders
    Route::get('/orders', [AdminController::class, 'indexOrders'])->name('orders.index');

    // Other Admin Routes (Placeholders)
    Route::get('/department', function () {
        return view('admin.department');
    })->name('department');
    Route::get('/product', function () {
        return view('admin.product');
    })->name('product');
    Route::get('/report', function () {
        return view('admin.report');
    })->name('report');

    // Summary Votes with Controller Logic
    Route::get('/approve-summary-votes', [AdminController::class, 'approveSummaryVotes'])->name('approve_summary_votes');

    // Orders Actions
    Route::get('/orders/{id}', [AdminController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update_status');

    // Request Items Actions
    Route::post('/request-items/{id}/reject', [AdminController::class, 'rejectRequestItem'])->name('request_items.reject');
    Route::post('/request-items/{id}/approve', [AdminController::class, 'approveRequestItem'])->name('request_items.approve');
    Route::post('/request-items/{id}/note', [AdminController::class, 'updateAggregationItemNote'])->name('request_items.update_note');
});