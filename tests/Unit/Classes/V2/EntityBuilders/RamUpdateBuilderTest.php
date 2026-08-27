<?php

use App\Classes\V2\EntityBuilders\RamUpdateBuilder;
use App\Enums\RamUpdateType;

it('defaults to a CHANGE update marked as master data', function () {
    $result = RamUpdateBuilder::make('dataset_123')->build();

    expect($result)->toMatchArray([
        'dataset_id' => 'dataset_123',
        'ram_update_type_id' => 'CHANGE',
        'is_master_data' => true,
    ]);
});

it('includes organisation_id and user_id from config', function () {
    config(['pso-services.settings.organisation_id' => '99', 'pso-services.settings.service_name' => 'Test Service']);

    $result = RamUpdateBuilder::make('dataset_123')->build();

    expect($result['organisation_id'])->toBe('99');
    expect($result['user_id'])->toBe('Test Service');
    expect($result['requesting_app_instance_id'])->toBe('Test Service');
});

it('allows overriding the update type and is_master_data', function () {
    $result = RamUpdateBuilder::make('dataset_123')
        ->updateType(RamUpdateType::LOAD)
        ->isMasterData(false)
        ->build();

    expect($result['ram_update_type_id'])->toBe('LOAD');
    expect($result['is_master_data'])->toBeFalse();
});

it('excludes description when not set', function () {
    $result = RamUpdateBuilder::make('dataset_123')->build();

    expect($result)->not->toHaveKey('description');
});

it('includes description when set', function () {
    $result = RamUpdateBuilder::make('dataset_123')->description('Add 2 regions')->build();

    expect($result['description'])->toBe('Add 2 regions');
});
