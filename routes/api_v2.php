<?php

use App\Http\Controllers\Api\V2\ActivityController;
use App\Http\Controllers\Api\V2\ActivityStatusController;
use App\Http\Controllers\Api\V2\AppointmentController;
use App\Http\Controllers\Api\V2\AssistController;
use App\Http\Controllers\Api\V2\HealthCheckController;
use App\Http\Controllers\Api\V2\ResourceController;
use App\Http\Controllers\Api\V2\ResourceEventController;
use App\Http\Controllers\Api\V2\ResourceShiftController;
use App\Http\Controllers\Api\V2\ResourceUnavailabilityController;
use App\Http\Controllers\Api\V2\ScheduleExceptionController;
use App\Http\Controllers\Api\V2\TravelController;

Route::post('/health-check', [HealthCheckController::class, 'check'])->name('v2.health-check');

// travel routes
Route::post('/travelanalyzer', [TravelController::class, 'store'])->name('v2.travel.store');
Route::post('/travelanalyzerservice', [TravelController::class, 'update'])->name('v2.travel.update');
Route::get('/travelanalyzer/{id}', [TravelController::class, 'show'])->name('v2.travel.show');

// activity routes
Route::patch('/activity/{activityId}/status', [ActivityStatusController::class, 'update'])->name('v2.activity.status.update');
Route::delete('/activity/', [ActivityController::class, 'destroy'])->name('v2.activity.destroy');

// assist routes
Route::delete('/delete', [AssistController::class, 'destroy'])->name('v2.assist.destroy');
Route::post('/load', [AssistController::class, 'store'])->name('v2.assist.load');
Route::patch('/rota', [AssistController::class, 'update'])->name('v2.assist.rota');
Route::get('/usage', [AssistController::class, 'show'])->name('v2.assist.usage');

// appointment routes
Route::post('/appointment', [AppointmentController::class, 'store'])->name('v2.appointment.store');
Route::post('/appointment/{appointmentRequestId}', [AppointmentController::class, 'check'])->name('v2.appointment.check');
Route::patch('/appointment/{appointmentRequestId}', [AppointmentController::class, 'update'])->name('v2.appointment.update');
Route::delete('/appointment/{appointmentRequestId}', [AppointmentController::class, 'destroy'])->name('v2.appointment.destroy');
Route::get('/appointment/{appointmentRequestId}', [AppointmentController::class, 'show'])->name('v2.appointment.show');

// resource routes
Route::get('/resource', [ResourceController::class, 'index'])->name('v2.resource.index');
Route::get('/resource/{resourceId}', [ResourceController::class, 'show'])->name('v2.resource.show');
Route::post('/resource/{resourceId}/event', [ResourceEventController::class, 'store'])->name('v2.resource.event.store');
Route::patch('/resource/{resourceId}/shift', [ResourceShiftController::class, 'update'])->name('v2.resource.shift.update');
Route::post('/resource/unavailability', [ResourceUnavailabilityController::class, 'store'])->name('v2.resource.unavailability.store');
Route::patch('/resource/unavailability/{unavailabilityId}', [ResourceUnavailabilityController::class, 'update'])->name('v2.resource.unavailability.update');

// exception
Route::post('/exception', [ScheduleExceptionController::class, 'store'])->name('v2.exception.store');
