<?php

use App\Helpers\Stubs\Resource;

it('builds a RAM_Resource entity with a derived id when none is given', function () {
    $result = Resource::make([
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
    ], 43.65107, -79.347015);

    expect($result['RAM_Resource'])->toBe([
        'id' => 'JOHNSMITH',
        'ram_resource_class_id' => config('pso-services.defaults.resource.class_id'),
        'ram_resource_type_id' => 'FIELD_TECH',
        'ram_location_id_start' => 'JOHNSMITH',
        'ram_location_id_end' => 'JOHNSMITH',
        'first_name' => 'John',
        'surname' => 'Smith',
    ]);
});

it('uses an explicit resource_id when given', function () {
    $result = Resource::make([
        'resource_id' => 'RES-001',
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
    ], 1.0, 2.0);

    expect($result['RAM_Resource']['id'])->toBe('RES-001');
});

it('builds a RAM_Location keyed to the resource id', function () {
    $result = Resource::make([
        'resource_id' => 'RES-001',
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
    ], 43.65107, -79.347015);

    expect($result['RAM_Location'])->toBe([
        'id' => 'RES-001',
        'latitude' => 43.65107,
        'longitude' => -79.347015,
    ]);
});

it('builds RAM_Resource_Skill rows with the resource id as ram_resource_id, not the literal string "resource"', function () {
    $result = Resource::make([
        'resource_id' => 'RES-001',
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
        'skill' => ['ELECTRICAL', 'PLUMBING'],
    ], 1.0, 2.0);

    expect($result['RAM_Resource_Skill'])->toBe([
        ['ram_skill_id' => 'ELECTRICAL', 'ram_resource_id' => 'RES-001'],
        ['ram_skill_id' => 'PLUMBING', 'ram_resource_id' => 'RES-001'],
    ]);
});

it('builds RAM_Resource_Division rows with the resource id as ram_resource_id, not the literal string "resource"', function () {
    $result = Resource::make([
        'resource_id' => 'RES-001',
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
        'region' => ['NORTH'],
    ], 1.0, 2.0);

    expect($result['RAM_Resource_Division'])->toBe([
        ['ram_resource_id' => 'RES-001', 'ram_division_id' => 'NORTH'],
    ]);
});

it('returns empty skill/division lists when none are given', function () {
    $result = Resource::make([
        'resource_id' => 'RES-001',
        'first_name' => 'John',
        'surname' => 'Smith',
        'resource_type_id' => 'FIELD_TECH',
    ], 1.0, 2.0);

    expect($result['RAM_Resource_Skill'])->toBe([]);
    expect($result['RAM_Resource_Division'])->toBe([]);
});
