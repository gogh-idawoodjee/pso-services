<?php

use App\Classes\V2\PsoClient;
use App\DataTransferObjects\PsoContext;
use App\Services\V2\RegionService;
use Illuminate\Http\JsonResponse;

function regionContext(array $dataOverrides = [], ?string $token = null): PsoContext
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

it('returns a dry-run payload without a token', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')
        ->once()
        ->with(Mockery::any(), true)
        ->andReturnUsing(fn ($payload) => ['payloadToPso' => ['DsModelling' => $payload]]);
    $psoClient->shouldNotReceive('sendToPso');

    $response = (new RegionService($psoClient))->createDivisions(regionContext());

    expect($response->status())->toBe(202);
});

it('builds a single RAM_Division object (not a list) for one region', function () {
    $captured = null;
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')
        ->andReturnUsing(function ($payload) use (&$captured) {
            $captured = $payload;

            return ['DsModelling' => $payload];
        });
    $psoClient->shouldNotReceive('sendToPso');

    (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH']]));

    expect($captured['RAM_Division'])->toBe(['id' => 'NORTH', 'description' => 'north', 'send' => true]);
});

it('builds a list of RAM_Division rows for multiple regions', function () {
    $captured = null;
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')
        ->andReturnUsing(function ($payload) use (&$captured) {
            $captured = $payload;

            return ['DsModelling' => $payload];
        });

    (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH', 'SOUTH']]));

    expect($captured['RAM_Division'])->toHaveCount(2);
    expect(collect($captured['RAM_Division'])->pluck('id')->all())->toBe(['NORTH', 'SOUTH']);
});

it('uses per-region descriptions only when the count matches', function () {
    $captured = null;
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')
        ->andReturnUsing(function ($payload) use (&$captured) {
            $captured = $payload;

            return ['DsModelling' => $payload];
        });

    (new RegionService($psoClient))->createDivisions(regionContext([
        'regions' => ['NORTH', 'SOUTH'],
        'descriptions' => ['Northern territory'],
    ]));

    expect(collect($captured['RAM_Division'])->pluck('description')->all())->toBe(['north', 'south']);
});

it('includes RAM_Division_Type when regionCategory is set', function () {
    $captured = null;
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')
        ->andReturnUsing(function ($payload) use (&$captured) {
            $captured = $payload;

            return ['DsModelling' => $payload];
        });

    (new RegionService($psoClient))->createDivisions(regionContext([
        'regions' => ['NORTH'],
        'regionCategory' => 'PROVINCE',
    ]));

    expect($captured['RAM_Division_Type'])->toBe(['id' => 'PROVINCE', 'description' => 'province']);
    expect($captured['RAM_Division']['ram_division_type_id'])->toBe('PROVINCE');
});

it('commits to PSO when a token is present', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('buildModellingPayload')->andReturnUsing(fn ($payload, $wrap = false) => ['DsModelling' => $payload]);
    $psoClient->shouldReceive('sendToPso')
        ->once()
        ->andReturn(new JsonResponse(['DsModelling' => []], 200));

    $response = (new RegionService($psoClient))->createDivisions(regionContext(['regions' => ['NORTH']], 'tok-123'));

    expect($response->status())->toBe(200);
});
