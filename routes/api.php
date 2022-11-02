<?php

//use App\Http\Controllers\PSOActivityController;
use App\Http\Controllers\PSOActivitySLAController;
use App\Http\Controllers\PSOActivityStatusController;
use App\Http\Controllers\PSOAssistController;
use App\Http\Controllers\PSOCommitController;
use App\Http\Controllers\PSOResourceController;
use App\Http\Controllers\PSOResourceEventController;
use App\Http\Controllers\PSOResourceShiftController;
use App\Http\Controllers\PSOUnavailabilityController;
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
// commit
Route::patch('/commit', [PSOCommitController::class, 'update']);
Route::post('/commit/test', [PSOCommitController::class, 'store']);

// assist
Route::post('/load', [PSOAssistController::class, 'store']);
Route::patch('/rotatodse', [PSOAssistController::class, 'update']);
Route::get('/usage', [PSOAssistController::class, 'index']);

// activity
Route::delete('/activity/{activity_id}/sla', [PSOActivitySLAController::class, 'destroy']);
Route::patch('/activity/{activity_id}/{status}', [PSOActivityStatusController::class, 'update']);


// resource (???)
Route::patch('/resource/{resource_id}/manualschedule', [PSOResourceShiftController::class, 'update']);
Route::post('/resource/{resource_id}/event', [PSOResourceEventController::class, 'store']);
Route::post('/resource/{resource_id}/unavailability', [PSOUnavailabilityController::class, 'store']);
Route::delete('/unavailability/{unavailability_id}/delete', [PSOUnavailabilityController::class, 'destroy']);
Route::get('/resource/', [PSOResourceController::class, 'index']);
Route::get('/resource/{resource_id}', [PSOResourceController::class, 'show']);


//Route::patch('/unavailability/{unavailability_id}', [PSOUnavailabilityController::class, 'update']); // doesn't exist yet
