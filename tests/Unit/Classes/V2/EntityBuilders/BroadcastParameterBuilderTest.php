<?php

use App\Classes\V2\EntityBuilders\BroadcastParameterBuilder;
use App\Enums\BroadcastParameterType;

it('finalizes using the enum value when given a BroadcastParameterType', function () {
    $result = BroadcastParameterBuilder::make()
        ->name(BroadcastParameterType::MEDIATYPE)
        ->value('application/json')
        ->finalize('broadcast-1');

    expect($result)->toBe([
        'broadcast_id' => 'broadcast-1',
        'parameter_name' => 'mediatype',
        'parameter_value' => 'application/json',
    ]);
});

it('finalizes using a raw string parameter name', function () {
    $result = BroadcastParameterBuilder::make()
        ->name('some_custom_param')
        ->value('custom value')
        ->finalize('broadcast-1');

    expect($result)->toBe([
        'broadcast_id' => 'broadcast-1',
        'parameter_name' => 'some_custom_param',
        'parameter_value' => 'custom value',
    ]);
});
