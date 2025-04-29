<?php

use App\Http\Controllers\Api\V2\ActivityStatusController;
use App\Http\Controllers\Api\V2\AppointmentController;
use App\Http\Controllers\Api\V2\AssistController;
use App\Http\Controllers\Api\V2\HealthCheckController;
use App\Http\Controllers\Api\V2\TravelController;

Route::post('/health-check', [HealthCheckController::class, 'check']);

// travel routes
Route::post('/travelanalyzer', [TravelController::class, 'store']); // this is the main service -> send data, returns an ID of some sort
Route::post('/travelanalyzerservice', [TravelController::class, 'update']); // this is the broadcast listener
Route::get('/travelanalyzer/{id}', [TravelController::class, 'show']); // this is the return service that returns your details once ready


//Activity routes
Route::patch('/activity/{activityId}/status', [ActivityStatusController::class, 'update']); // added /status in case someday there is an update to the actual activity object itself


// Assist routes
Route::delete('/delete', [AssistController::class, 'destroy']);
Route::post('/load', [AssistController::class, 'store']);
Route::patch('/rota', [AssistController::class, 'update']);


// appointemnt routes
Route::post('/appointment', [AppointmentController::class, 'store']);
Route::post('/appointment/{appointmentRequestId}', [AppointmentController::class, 'check']);
Route::patch('/appointment/{appointmentRequestId}', [AppointmentController::class, 'update']);
Route::delete('/appointment/{appointmentRequestId}', [AppointmentController::class, 'destroy']);
Route::get('/appointment/{appointmentRequestId}', [AppointmentController::class, 'show']);
