<?php

use App\Http\Controllers\Api\V2\HealthCheckController;
use App\Http\Controllers\Api\V2\TravelController;

Route::post('/health-check', [HealthCheckController::class, 'check']);
Route::post('/travelanalyzer', [TravelController::class, 'store']); // this is the main service -> send data, returns an ID of some sort
Route::post('/travelanalyzerservice', [TravelController::class, 'update']); // this is the broadcast listener
Route::get('/travelanalyzer/{id}', [TravelController::class, 'show']); // this is the return service that returns your details once ready
