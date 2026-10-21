<?php

use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Zadex Support Tickets
|--------------------------------------------------------------------------
*/

Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketApiController::class, 'index']);
    Route::post('/', [TicketApiController::class, 'store']);
    Route::get('/{ticketNumber}', [TicketApiController::class, 'show']);
    Route::put('/{ticketNumber}', [TicketApiController::class, 'update']);
    Route::patch('/{ticketNumber}', [TicketApiController::class, 'update']);
    Route::delete('/{ticketNumber}', [TicketApiController::class, 'destroy']);
    Route::post('/{ticketNumber}/reply', [TicketApiController::class, 'reply']);
});
