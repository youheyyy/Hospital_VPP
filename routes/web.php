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
    Route::get('/request/{id}/detail', [DepartmentController::class, 'getRequestDetail'])->name('request.detail');
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

    // Product Management (CRUD)
    Route::get('/product', [AdminController::class, 'indexProducts'])->name('product');
    Route::post('/product', [AdminController::class, 'storeProduct'])->name('product.store');
    Route::put('/product/{id}', [AdminController::class, 'updateProduct'])->name('product.update');
    Route::delete('/product/{id}', [AdminController::class, 'destroyProduct'])->name('product.destroy');

    // Management Functionality (4 Tabs) - Renamed from /system
    Route::get('/management', [AdminController::class, 'indexManagement'])->name('management');

    // Management CRUD Routes
    Route::post('/management/categories', [AdminController::class, 'storeCategory'])->name('management.categories.store');
    Route::put('/management/categories/{id}', [AdminController::class, 'updateCategory'])->name('management.categories.update');
    Route::delete('/management/categories/{id}', [AdminController::class, 'destroyCategory'])->name('management.categories.destroy');

    Route::post('/management/suppliers', [AdminController::class, 'storeSupplier'])->name('management.suppliers.store');
    Route::put('/management/suppliers/{id}', [AdminController::class, 'updateSupplier'])->name('management.suppliers.update');
    Route::delete('/management/suppliers/{id}', [AdminController::class, 'destroySupplier'])->name('management.suppliers.destroy');

    Route::post('/management/departments', [AdminController::class, 'storeDepartment'])->name('management.departments.store');
    Route::put('/management/departments/{id}', [AdminController::class, 'updateDepartment'])->name('management.departments.update');
    Route::delete('/management/departments/{id}', [AdminController::class, 'destroyDepartment'])->name('management.departments.destroy');

    Route::post('/management/users', [AdminController::class, 'storeUser'])->name('management.users.store');
    Route::put('/management/users/{id}', [AdminController::class, 'updateUser'])->name('management.users.update');
    Route::delete('/management/users/{id}', [AdminController::class, 'destroyUser'])->name('management.users.destroy');

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

    // Print and Export
    Route::get('/aggregation/print', [AdminController::class, 'printAggregation'])->name('aggregation.print');
    Route::get('/aggregation/export-excel', [AdminController::class, 'exportAggregationExcel'])->name('aggregation.export_excel');
});