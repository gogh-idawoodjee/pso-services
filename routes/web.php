<?php

use App\Http\Livewire\Getcrazy\PsoResourceShow;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PSOEnvrionmentController;

use App\Http\Controllers\PSOScheduleController;
use App\Http\Livewire\Getcrazy\PsoSchedule;
use App\Http\Livewire\Getcrazy\PsoResource;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/config/environment', [PSOEnvrionmentController::class, 'index']);
Route::get('/getcrazy/schedule', PsoSchedule::class);
Route::get('/getcrazy/schedulebreakdown', [PSOScheduleController::class, 'index']);
//Route::get('/getcrazy/resource', [PSOResourceController::class, 'index']);
Route::get('/getcrazy/resource', PsoResource::class);
Route::get('/getcrazy/resource/{resource_id}', PsoResourceShow::class);


