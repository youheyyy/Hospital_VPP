<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\DepartmentController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// SuperAdmin routes
Route::middleware(['auth', 'role:SuperAdmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [SuperAdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/change-password', [SuperAdminController::class, 'changePassword'])->name('users.change-password');
    Route::post('/users/{user}/reset-password', [SuperAdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/toggle-status', [SuperAdminController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser'])->name('users.delete');

    // Data Management
    Route::get('/data-management', [SuperAdminController::class, 'dataManagement'])->name('data-management');
    Route::post('/backup', [SuperAdminController::class, 'createBackup'])->name('backup.create');
    Route::post('/import', [SuperAdminController::class, 'importData'])->name('import');
    Route::get('/export-template/{type}', [SuperAdminController::class, 'exportTemplate'])->name('export-template');
});

// Admin routes
Route::middleware(['auth', 'role:SuperAdmin,Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/consolidated', [AdminController::class, 'consolidated'])->name('consolidated');
    Route::get('/consolidated/export', [AdminController::class, 'exportConsolidated'])->name('consolidated.export');
    Route::get('/consolidated/print', [AdminController::class, 'printConsolidated'])->name('consolidated.print');
    Route::post('/consolidated/update-note', [AdminController::class, 'updateNote'])->name('consolidated.update_note');
});

// Department routes
Route::middleware(['auth', 'role:Department'])->prefix('department')->name('department.')->group(function () {
    Route::get('/', [DepartmentController::class, 'index'])->name('index');
    Route::get('/history', [DepartmentController::class, 'history'])->name('history');
    Route::get('/history/print', [DepartmentController::class, 'printHistory'])->name('history.print');
    Route::post('/store', [DepartmentController::class, 'store'])->name('store');
    Route::post('/order/{id}/update-quantity', [DepartmentController::class, 'updateQuantity'])->name('order.update-quantity');
    Route::delete('/order/{id}/delete', [DepartmentController::class, 'deleteOrder'])->name('order.delete');
    Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('destroy');
});