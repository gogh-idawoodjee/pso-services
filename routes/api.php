<?php

use App\Http\Controllers\Api\V1\PSOActivityController;
use App\Http\Controllers\Api\V1\PSOActivitySLAController;
use App\Http\Controllers\Api\V1\PSOActivityStatusController;
use App\Http\Controllers\Api\V1\PSOAppointmentController;
use App\Http\Controllers\Api\V1\PSOAssistController;
use App\Http\Controllers\Api\V1\PSOCommitController;
use App\Http\Controllers\Api\V1\PSOExceptionController;
use App\Http\Controllers\Api\V1\PSORegionController;
use App\Http\Controllers\Api\V1\PSOResourceController;
use App\Http\Controllers\Api\V1\PSOResourceEventController;
use App\Http\Controllers\Api\V1\PSOResourceRelocationController;
use App\Http\Controllers\Api\V1\PSOResourceShiftController;
use App\Http\Controllers\Api\V1\PSOSandboxController;
use App\Http\Controllers\Api\V1\PSOTravelLogController;
use App\Http\Controllers\Api\V1\PSOUnavailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
})->name('v1.user');

// commit
Route::post('/commit', [PSOCommitController::class, 'update'])->name('v1.commit.update');
Route::post('/commit/test', [PSOCommitController::class, 'store'])->name('v1.commit.store');

// travel
Route::post('/travelanalyzer', [PSOTravelLogController::class, 'store'])->name('v1.travel.store');
Route::post('/travelanalyzerservice', [PSOTravelLogController::class, 'update'])->name('v1.travel.update');

// assist
Route::post('/load', [PSOAssistController::class, 'store'])->name('v1.assist.load');
Route::patch('/rotatodse', [PSOAssistController::class, 'update'])->name('v1.assist.rota');
Route::get('/usage', [PSOAssistController::class, 'index'])->name('v1.assist.usage');
Route::delete('/delete', [PSOAssistController::class, 'destroy'])->name('v1.assist.destroy');
Route::delete('/cleanup', [PSOAssistController::class, 'cleanup'])->name('v1.assist.cleanup');

// exception
Route::post('/exception', [PSOExceptionController::class, 'store'])->name('v1.exception.store');

// activity
Route::delete('/activity/{activity_id}/sla', [PSOActivitySLAController::class, 'destroy'])->name('v1.activity.sla.destroy');
Route::patch('/activity/{activity_id}/{status}', [PSOActivityStatusController::class, 'update'])->name('v1.activity.status.update');
Route::post('/activity/', [PSOActivityController::class, 'store'])->name('v1.activity.store');
Route::delete('/activity/', [PSOActivityController::class, 'destroyMulti'])->name('v1.activity.destroy-multi');
Route::delete('/activity/{activity_id}', [PSOActivityController::class, 'destroy'])->name('v1.activity.destroy');

// appointment
Route::post('/appointment', [PSOAppointmentController::class, 'store'])->name('v1.appointment.store');
Route::post('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'show'])->name('v1.appointment.check');
Route::patch('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'update'])->name('v1.appointment.update');
Route::delete('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'destroy'])->name('v1.appointment.destroy');
Route::get('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'index'])->name('v1.appointment.show');

// region
Route::post('/region', [PSORegionController::class, 'store'])->name('v1.region.store');

// resource
Route::patch('/resource/{resource_id}/shift', [PSOResourceShiftController::class, 'update'])->name('v1.resource.shift.update');
Route::post('/resource/{resource_id}/event', [PSOResourceEventController::class, 'store'])->name('v1.resource.event.store');
Route::post('/resource/{resource_id}/relocate', [PSOResourceRelocationController::class, 'store'])->name('v1.resource.relocate.store');
Route::post('/resource/{resource_id}/unavailability', [PSOUnavailabilityController::class, 'store'])->name('v1.resource.unavailability.store');
Route::post('/resource/', [PSOResourceController::class, 'store'])->name('v1.resource.store');
Route::get('/resource/', [PSOResourceController::class, 'index'])->name('v1.resource.index');
Route::get('/resource/{resource_id}', [PSOResourceController::class, 'show'])->name('v1.resource.show');

// unavailability
Route::delete('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'destroy'])->name('v1.unavailability.destroy');
Route::patch('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'update'])->name('v1.unavailability.update');

// load test
Route::post('/loadtest', [PSOSandboxController::class, 'runLoadTestJob'])->name('v1.loadtest');
