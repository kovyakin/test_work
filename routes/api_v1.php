<?php


use App\Http\Controllers\Api\v1\BookingController;
use App\Http\Controllers\Api\v1\ResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {
//    Resources
    Route::get('/resources', [ResourceController::class, 'index'])
        ->middleware('ability:resources:get');

    Route::post('/resources', [ResourceController::class, 'store'])
        ->middleware('ability:resources:create');

//    Bookings
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('ability:booking:create');

    Route::get('/resources/{id}/bookings', [BookingController::class, 'index'])
        ->middleware('ability:booking:show');

    Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])
        ->middleware('ability:booking:destroy');
});

