<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.tickets.index');
});

Route::get('/login', fn() => redirect()->route('admin.login'))->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Auth Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('admin.tickets.index');
    });

    // Authenticated Dashboard Routes
    Route::middleware('auth')->prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/create', [DashboardController::class, 'create'])->name('create');
        Route::post('/', [DashboardController::class, 'store'])->name('store');
        Route::get('/{ticket}', [DashboardController::class, 'show'])->name('show');
        Route::get('/{ticket}/edit', [DashboardController::class, 'edit'])->name('edit');
        Route::put('/{ticket}', [DashboardController::class, 'update'])->name('update');
        Route::post('/{ticket}/reply', [DashboardController::class, 'reply'])->name('reply');
        Route::patch('/{ticket}/status', [DashboardController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{ticket}', [DashboardController::class, 'destroy'])->name('destroy');
    });
});
