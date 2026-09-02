<?php

use App\Models\V2\PSOTravelLog;

it('persists warnings as an array without an "Array to string conversion" error', function () {
    $log = PSOTravelLog::create([
        'id' => 'test-travel-log-id',
        'status' => 'created',
        'warnings' => ['Google Distance Matrix API request failed with status ZERO_RESULTS.'],
    ]);

    $log->refresh();

    expect($log->warnings)->toBe(['Google Distance Matrix API request failed with status ZERO_RESULTS.']);
});
