<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/token', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('device-tokens')->group(function () {
        Route::post('/', [DeviceTokenController::class, 'store']);
        Route::delete('/', [DeviceTokenController::class, 'destroy']);
    });

    Route::prefix('notifications')->group(function () {
        Route::post('/send', [DeviceTokenController::class, 'sendNotification']);
    });

});
