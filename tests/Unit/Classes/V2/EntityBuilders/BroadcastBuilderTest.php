<?php

use App\Classes\V2\EntityBuilders\BroadcastBuilder;
use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;

it('sums multiple allocation types into a single bitmask value', function () {
    $result = BroadcastBuilder::make()
        ->allocationType([BroadcastAllocationType::DYNAMIC_SCHEDULING, BroadcastAllocationType::MANUAL_SCHEDULING])
        ->planType(BroadcastPlanType::COMPLETE)
        ->build();

    expect($result['Broadcast']['allocation_type'])->toBe(5);
});

it('accepts a single allocation type', function () {
    $result = BroadcastBuilder::make()
        ->allocationType(BroadcastAllocationType::SCHEDULING_TRAVEL_ANALYSER)
        ->planType(BroadcastPlanType::COMPLETE)
        ->build();

    expect($result['Broadcast']['allocation_type'])->toBe(16);
});

it('auto-generates an id when none is provided', function () {
    $result = BroadcastBuilder::make()
        ->planType(BroadcastPlanType::COMPLETE)
        ->build();

    expect($result['Broadcast']['id'])->toBeString()->not->toBeEmpty();
});

it('uses a client-supplied id when provided', function () {
    $result = BroadcastBuilder::make()
        ->id('my-custom-broadcast-id')
        ->planType(BroadcastPlanType::COMPLETE)
        ->build();

    expect($result['Broadcast']['id'])->toBe('my-custom-broadcast-id');
});

it('excludes unset optional fields from the built Broadcast row', function () {
    $result = BroadcastBuilder::make()
        ->planType(BroadcastPlanType::COMPLETE)
        ->build();

    expect($result['Broadcast'])
        ->not->toHaveKeys([
            'description',
            'minimum_plan_quality',
            'minimum_step_interval',
            'expiry_datetime',
            'input_reference_id',
            'maximum_frequency',
            'maximum_wait',
            'minimum_visit_status',
            'time_filter_start',
            'time_filter_end',
            'allocation_type',
        ]);
});

it('includes optional fields when set', function () {
    $result = BroadcastBuilder::make()
        ->planType(BroadcastPlanType::COMPLETE)
        ->description('Test broadcast')
        ->minimumPlanQuality(80.0)
        ->minimumStepInterval(1)
        ->expiryDatetime('2026-01-01T00:00:00Z')
        ->inputReferenceId('ref-1')
        ->maximumFrequency('PT5M')
        ->maximumWait('PT30M')
        ->minimumVisitStatus(2)
        ->timeFilterStart('2026-01-01T00:00:00Z')
        ->timeFilterEnd('2026-01-02T00:00:00Z')
        ->build();

    expect($result['Broadcast'])
        ->toMatchArray([
            'description' => 'Test broadcast',
            'minimum_plan_quality' => 80.0,
            'minimum_step_interval' => 1,
            'expiry_datetime' => '2026-01-01T00:00:00Z',
            'input_reference_id' => 'ref-1',
            'maximum_frequency' => 'PT5M',
            'maximum_wait' => 'PT30M',
            'minimum_visit_status' => 2,
            'time_filter_start' => '2026-01-01T00:00:00Z',
            'time_filter_end' => '2026-01-02T00:00:00Z',
        ]);
});

it('defaults active to true and allows overriding to false', function () {
    $active = BroadcastBuilder::make()->planType(BroadcastPlanType::COMPLETE)->build();
    expect($active['Broadcast']['active'])->toBeTrue();

    $inactive = BroadcastBuilder::make()->planType(BroadcastPlanType::COMPLETE)->active(false)->build();
    expect($inactive['Broadcast']['active'])->toBeFalse();
});
