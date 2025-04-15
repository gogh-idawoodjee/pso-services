<?php

use App\Http\Controllers\Api\V2\HealthCheckController;

Route::post('/health-check', [HealthCheckController::class, 'check']);
