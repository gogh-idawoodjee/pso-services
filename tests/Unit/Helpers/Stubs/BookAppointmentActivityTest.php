<?php

use App\Helpers\Stubs\BookAppointmentActivity;

it('strips the suffix from Activity.id and Activity.location_id', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity' => ['id' => 'ACT-001_AB', 'location_id' => 'ACT-001_AB'],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity'])->toMatchArray(['id' => 'ACT-001', 'location_id' => 'ACT-001']);
});

it('strips the suffix from Activity_Status.activity_id', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity_Status' => ['activity_id' => 'ACT-001_AB', 'status_id' => 5],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity_Status']['activity_id'])->toBe('ACT-001');
    expect($result['Activity_Status']['status_id'])->toBe(5);
});

it('strips the suffix from Location.id and Location_Region rows when present', function () {
    $result = BookAppointmentActivity::finalize([
        'Location' => ['id' => 'ACT-001_AB', 'latitude' => 1.0, 'longitude' => 2.0],
        'Location_Region' => [
            ['location_id' => 'ACT-001_AB', 'region_id' => 'NORTH'],
            ['location_id' => 'ACT-001_AB', 'region_id' => 'SOUTH'],
        ],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Location']['id'])->toBe('ACT-001');
    expect($result['Location_Region'])->toBe([
        ['location_id' => 'ACT-001', 'region_id' => 'NORTH'],
        ['location_id' => 'ACT-001', 'region_id' => 'SOUTH'],
    ]);
});

it('overrides Activity_SLA activity_id and the SLA window when present', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity_SLA' => [
            'activity_id' => 'ACT-001_AB',
            'sla_type_id' => 'STANDARD',
            'datetime_start' => 'old-start',
            'datetime_end' => 'old-end',
        ],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity_SLA'])->toBe([
        'activity_id' => 'ACT-001',
        'sla_type_id' => 'STANDARD',
        'datetime_start' => '2026-05-01T08:00:00',
        'datetime_end' => '2026-05-01T12:00:00',
    ]);
});

it('strips the suffix from every Activity_Skill row when present', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity_Skill' => [
            ['skill_id' => 'ELECTRICAL', 'activity_id' => 'ACT-001_AB'],
            ['skill_id' => 'PLUMBING', 'activity_id' => 'ACT-001_AB'],
        ],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity_Skill'])->toBe([
        ['skill_id' => 'ELECTRICAL', 'activity_id' => 'ACT-001'],
        ['skill_id' => 'PLUMBING', 'activity_id' => 'ACT-001'],
    ]);
});

it('leaves absent optional entities untouched (PRIVATE activity class, no location)', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity' => ['id' => 'ACT-001_AB'],
        'Activity_Status' => ['activity_id' => 'ACT-001_AB'],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result)->not->toHaveKeys(['Location', 'Location_Region', 'Activity_SLA', 'Activity_Skill']);
});

it('raises base_value by the default 1.5x multiplier when none is given', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity' => ['id' => 'ACT-001_AB', 'base_value' => 2000],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity']['base_value'])->toBe(3000);
});

it('raises base_value by a given multiplier, rounded to the nearest integer', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity' => ['id' => 'ACT-001_AB', 'base_value' => 999],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00', 2.0);

    expect($result['Activity']['base_value'])->toBe(1998);
});

it('leaves base_value untouched when not present on the activity', function () {
    $result = BookAppointmentActivity::finalize([
        'Activity' => ['id' => 'ACT-001_AB'],
    ], 'ACT-001', '2026-05-01T08:00:00', '2026-05-01T12:00:00');

    expect($result['Activity'])->not->toHaveKey('base_value');
});
