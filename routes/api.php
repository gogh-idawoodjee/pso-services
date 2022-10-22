<?php

use App\Http\Controllers\PSOCommitController;
use App\Http\Controllers\PSOResourceController;
use App\Http\Controllers\PSOResourceEventController;
use App\Http\Controllers\PSOResourceShiftController;
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

Route::patch('/commit', [PSOCommitController::class, 'update']);
Route::patch('/resource/{resource_id}/manualschedule', [PSOResourceShiftController::class, 'update']);
Route::post('/resource/{resource_id}/event', [PSOResourceEventController::class, 'store']);
Route::get('/resource/', [PSOResourceController::class, 'index']);
Route::get('/resource/{resource_id}', [PSOResourceController::class, 'show']);


