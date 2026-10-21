<?php

use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Zadex Support Tickets
|--------------------------------------------------------------------------
*/

Route::prefix('tickets')->group(function () {
    Route::post('/', [TicketApiController::class, 'store']);
    Route::get('/{ticketNumber}', [TicketApiController::class, 'show']);
});
