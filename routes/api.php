<?php

use App\Http\Controllers\Api\V1\PSOExceptionController;
use App\Http\Controllers\Api\V1\PSORegionController;
use App\Http\Controllers\Api\V1\PSOResourceController;
use App\Http\Controllers\Api\V1\PSOResourceEventController;
use App\Http\Controllers\Api\V1\PSOResourceRelocationController;
use App\Http\Controllers\Api\V1\PSOResourceShiftController;
use App\Http\Controllers\Api\V1\PSOSandboxController;
use App\Http\Controllers\Api\V1\PSOTravelLogController;
use App\Http\Controllers\Api\V1\PSOUnavailabilityController;
use App\Http\Controllers\Api\V1\PSOActivityController;
use App\Http\Controllers\Api\V1\PSOActivitySLAController;
use App\Http\Controllers\Api\V1\PSOActivityStatusController;
use App\Http\Controllers\Api\V1\PSOAppointmentController;
use App\Http\Controllers\Api\V1\PSOAssistController;
use App\Http\Controllers\Api\V1\PSOCommitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::middleware(['treblle'])->group(function () {
// commit
Route::post('/commit', [PSOCommitController::class, 'update']);
Route::post('/commit/test', [PSOCommitController::class, 'store']);

// travel
Route::post('/travelanalyzer', [PSOTravelLogController::class, 'store']);
Route::post('/travelanalyzerservice', [PSOTravelLogController::class, 'update']); // this is the broadcast listener?

// assist
Route::post('/load', [PSOAssistController::class, 'store']);
Route::patch('/rotatodse', [PSOAssistController::class, 'update']);
Route::get('/usage', [PSOAssistController::class, 'index']);
Route::delete('/delete', [PSOAssistController::class, 'destroy']);
Route::delete('cleanup', [PSOAssistController::class, 'cleanup']);

// exception
Route::post('/exception', [PSOExceptionController::class, 'store']);

// activity
Route::delete('/activity/{activity_id}/sla', [PSOActivitySLAController::class, 'destroy']);
Route::patch('/activity/{activity_id}/{status}', [PSOActivityStatusController::class, 'update']);
Route::post('/activity/', [PSOActivityController::class, 'store']);
Route::delete('/activity/', [PSOActivityController::class, 'destroyMulti']);
Route::delete('/activity/{activity_id}', [PSOActivityController::class, 'destroy']);

// appointment
Route::post('/appointment', [PSOAppointmentController::class, 'store']); // getAppointment
Route::post('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'show']); // checkAppointed
Route::patch('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'update']); // acceptAppointment
Route::delete('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'destroy']); // declineAppointment
Route::get('/appointment/{appointment_request_id}', [PSOAppointmentController::class, 'index']); // getDetails

Route::post('/region', [PSORegionController::class, 'store']);

// resource (???)
Route::patch('/resource/{resource_id}/shift', [PSOResourceShiftController::class, 'update']);
Route::post('/resource/{resource_id}/event', [PSOResourceEventController::class, 'store']);
Route::post('/resource/{resource_id}/relocate', [PSOResourceRelocationController::class, 'store']);
Route::post('/resource/{resource_id}/unavailability', [PSOUnavailabilityController::class, 'store']);

Route::post('/resource/', [PSOResourceController::class, 'store']);

Route::get('/resource/', [PSOResourceController::class, 'index']);
Route::get('/resource/{resource_id}', [PSOResourceController::class, 'show']);


Route::delete('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'destroy']);
Route::patch('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'update']);


Route::post('/loadtest', [PSOSandboxController::class, 'runLoadTestJob']);
//});

//Route::patch('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'update']); // doesn't exist yet
