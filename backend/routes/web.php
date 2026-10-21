<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.tickets.index');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.tickets.index');
    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/{ticket}', [DashboardController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [DashboardController::class, 'reply'])->name('reply');
        Route::patch('/{ticket}/status', [DashboardController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{ticket}', [DashboardController::class, 'destroy'])->name('destroy');
    });
});
