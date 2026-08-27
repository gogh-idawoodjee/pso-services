<?php

use App\Classes\V2\PsoClient;
use App\Classes\V2\SendOrSimulateBuilder;
use App\DataTransferObjects\PsoContext;
use App\Services\V2\ResourceService;

function createResourceContext(array $dataOverrides = [], ?string $token = null): PsoContext
{
    return new PsoContext($token, [
        'environment' => [
            'sendToPso' => (bool) $token,
            'baseUrl' => 'https://example.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'account_001',
        ],
        'data' => array_replace([
            'resourceTypeId' => 'FIELD_TECH',
            'lat' => [43.65107],
            'long' => [-79.347015],
            'names' => ['John Smith'],
        ], $dataOverrides),
    ]);
}

function mockedResourcePsoClient(array &$capturedArgs = []): PsoClient
{
    $psoClient = Mockery::mock(PsoClient::class);

    $psoClient->shouldReceive('sendOrSimulateBuilder')
        ->andReturnUsing(fn () => new SendOrSimulateBuilder($psoClient));

    $psoClient->shouldReceive('executeSendOrSimulate')
        ->andReturnUsing(function ($builder) use (&$capturedArgs) {
            $capturedArgs = $builder->toSendOrSimulateArgs();

            return response()->json(['data' => [], 'status' => 200]);
        });

    return $psoClient;
}

it('uses the DsModelling schema wrapper', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext());

    expect($captured['useModellingSchema'])->toBeTrue();
});

it('builds a single RAM_Resource/RAM_Location object for one resource', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext());

    expect($captured['payload']['RAM_Resource'])->toBeArray()->not->toBeList();
    expect($captured['payload']['RAM_Resource']['first_name'])->toBe('John');
    expect($captured['payload']['RAM_Resource']['surname'])->toBe('Smith');
    expect($captured['payload']['RAM_Resource']['id'])->toBe('JOHNSMITH');
    expect($captured['payload']['RAM_Location'])->toBe(['id' => 'JOHNSMITH', 'latitude' => 43.65107, 'longitude' => -79.347015]);
});

it('builds a list of RAM_Resource rows for multiple resources', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext([
        'lat' => [1.0, 2.0],
        'long' => [3.0, 4.0],
        'names' => ['John Smith', 'Jane Doe'],
    ]));

    expect($captured['payload']['RAM_Resource'])->toHaveCount(2);
    expect(collect($captured['payload']['RAM_Resource'])->pluck('first_name')->all())->toBe(['John', 'Jane']);
});

it('generates a random name when fewer names are given than resources', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext([
        'lat' => [1.0, 2.0],
        'long' => [3.0, 4.0],
        'resourcesToCreate' => 2,
        'names' => ['John Smith'],
    ]));

    $resources = $captured['payload']['RAM_Resource'];
    expect($resources[0]['first_name'])->toBe('John');
    expect($resources[1]['first_name'])->not->toBeEmpty()->not->toBe('John');
});

it('uses an explicit resource id when given, falling back to a derived id otherwise', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext([
        'lat' => [1.0, 2.0],
        'long' => [3.0, 4.0],
        'names' => ['John Smith', 'Jane Doe'],
        'ids' => ['RES-001'],
    ]));

    $resources = $captured['payload']['RAM_Resource'];
    expect($resources[0]['id'])->toBe('RES-001');
    expect($resources[1]['id'])->toBe('JANEDOE');
});

it('bounds the count by the shortest of resourcesToCreate/lat/long', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext([
        'lat' => [1.0, 2.0, 3.0],
        'long' => [4.0, 5.0, 6.0],
        'resourcesToCreate' => 2,
        'names' => ['John Smith', 'Jane Doe', 'Bob Jones'],
    ]));

    expect($captured['payload']['RAM_Resource'])->toHaveCount(2);
});

it('flattens RAM_Resource_Skill and RAM_Resource_Division across the whole batch', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext([
        'lat' => [1.0, 2.0],
        'long' => [3.0, 4.0],
        'names' => ['John Smith', 'Jane Doe'],
        'skills' => ['ELECTRICAL'],
        'regions' => ['NORTH'],
    ]));

    expect($captured['payload']['RAM_Resource_Skill'])->toHaveCount(2);
    expect($captured['payload']['RAM_Resource_Division'])->toHaveCount(2);
});

it('omits RAM_Resource_Skill and RAM_Resource_Division when none are given', function () {
    $captured = [];
    $psoClient = mockedResourcePsoClient($captured);

    (new ResourceService($psoClient))->createResource(createResourceContext());

    expect($captured['payload'])->not->toHaveKeys(['RAM_Resource_Skill', 'RAM_Resource_Division']);
});
