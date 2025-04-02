<?php


use Illuminate\Support\Facades\Route;


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


Route::get('/', static function () {
    return redirect('https://out-of-jam.stoplight.io/docs/ish-pso-services/');
});
//
//Route::get('/dashboard', function () {
////    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

//Route::middleware('auth')->group(function () {
//    // I think all this has moved to the front-end project
////    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
////    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
////    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
////
////    Route::get('/environment', [EnvironmentController::class, 'index'])->name('env.index');
////    Route::get('/environment/{id}', [EnvironmentController::class, 'edit'])->name('env.edit');
////    Route::get('/assist/init', [AssistController::class, 'index'])->name('assist.index');
////    Route::get('/assist/rota', [AssistController::class, 'update'])->name('assist.update');
////    Route::get('/assist/usage', [AssistController::class, 'show'])->name('assist.show');
////    Route::get('/resource', [ResourceController::class, 'index'])->name('resource.show');
//});

//require __DIR__ . '/auth.php';
