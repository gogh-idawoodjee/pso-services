<?php

use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return redirect('https://out-of-jam.stoplight.io/docs/ish-pso-services/');
});
