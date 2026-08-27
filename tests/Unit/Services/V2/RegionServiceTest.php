<?php

use App\Classes\V2\PsoClient;
use App\Classes\V2\SendOrSimulateBuilder;
use App\DataTransferObjects\PsoContext;
use App\Services\V2\RegionService;

function regionContext(array $dataOverrides = [], string|null $token = null): PsoContext
{
    return new PsoContext($token, [
        'environment' => [
            'sendToPso' => (bool) $token,
            'baseUrl' => 'https://example.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'account_001',
        ],
        'data' => array_replace([
            'regions' => ['NORTH'],
        ], $dataOverrides),
    ]);
}

function mockedRegionPsoClient(array &$capturedArgs = []): PsoClient
{
    $psoClient = Mockery::mock(PsoClient::class);

    $psoClient->shouldReceive('sendOrSimulateBuilder')
        ->andReturnUsing(fn() => new SendOrSimulateBuilder($psoClient));

    $psoClient->shouldReceive('executeSendOrSimulate')
        ->andReturnUsing(function ($builder) use (&$capturedArgs) {
            $capturedArgs = $builder->toSendOrSimulateArgs();
            return response()->json(['data' => [], 'status' => 200]);
        });

    return $psoClient;
}

it('uses the DsModelling schema wrapper (not dsScheduleData)', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext());

    expect($captured['useModellingSchema'])->toBeTrue();
});

it('builds a single RAM_Division object (not a list) for one region', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH']]));

    expect($captured['payload']['RAM_Division'])->toBe(['id' => 'NORTH', 'description' => 'north', 'send' => true]);
});

it('builds a list of RAM_Division rows for multiple regions', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH', 'SOUTH']]));

    expect($captured['payload']['RAM_Division'])->toHaveCount(2);
    expect(collect($captured['payload']['RAM_Division'])->pluck('id')->all())->toBe(['NORTH', 'SOUTH']);
});

it('uses per-region descriptions only when the count matches', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext([
        'regions' => ['NORTH', 'SOUTH'],
        'descriptions' => ['Northern territory'],
    ]));

    expect(collect($captured['payload']['RAM_Division'])->pluck('description')->all())->toBe(['north', 'south']);
});

it('includes RAM_Division_Type when regionCategory is set', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext([
        'regions' => ['NORTH'],
        'regionCategory' => 'PROVINCE',
    ]));

    expect($captured['payload']['RAM_Division_Type'])->toBe(['id' => 'PROVINCE', 'description' => 'province']);
    expect($captured['payload']['RAM_Division']['ram_division_type_id'])->toBe('PROVINCE');
});

it('includes a RAM_Update header sourced from the dataset id', function () {
    $captured = [];
    $psoClient = mockedRegionPsoClient($captured);

    (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH']]));

    expect($captured['payload']['RAM_Update']['dataset_id'])->toBe('dataset_123');
});
