<?php

use App\Classes\V2\PsoClient;
use App\Classes\V2\SendOrSimulateBuilder;
use App\DataTransferObjects\PsoContext;
use App\Services\V2\LoadService;
use App\Services\V2\ScheduleService;

function loadContext(array $environmentOverrides = [], array $dataOverrides = []): PsoContext
{
    return new PsoContext('tok-123', [
        'environment' => array_replace([
            'sendToPso' => true,
            'baseUrl' => 'https://example.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'account_001',
        ], $environmentOverrides),
        'data' => array_replace([
            'dseDuration' => 120,
            'processType' => 'DYNAMIC',
            'keepPsoData' => true,
        ], $dataOverrides),
    ]);
}

function mockedPsoClient(): PsoClient
{
    $psoClient = Mockery::mock(PsoClient::class);

    $psoClient->shouldReceive('sendOrSimulateBuilder')
        ->andReturnUsing(fn () => new SendOrSimulateBuilder($psoClient));

    $psoClient->shouldReceive('executeSendOrSimulate')
        ->andReturn(response()->json(['data' => [], 'status' => 200]));

    return $psoClient;
}

function mockedPsoClientCapturingPayload(array &$capturedPayload): PsoClient
{
    $psoClient = Mockery::mock(PsoClient::class);

    $psoClient->shouldReceive('sendOrSimulateBuilder')
        ->andReturnUsing(fn () => new SendOrSimulateBuilder($psoClient));

    $psoClient->shouldReceive('executeSendOrSimulate')
        ->andReturnUsing(function ($builder) use (&$capturedPayload) {
            $capturedPayload = $builder->toSendOrSimulateArgs()['payload'];

            return response()->json(['data' => [], 'status' => 200]);
        });

    return $psoClient;
}

it('fetches existing schedule data when keepPsoData and sendToPso are both true', function () {
    $psoClient = mockedPsoClient();

    $scheduleService = Mockery::mock(ScheduleService::class);
    $scheduleService->shouldReceive('getScheduleData')
        ->once()
        ->with('https://example.ifs.cloud', 'dataset_123', 'tok-123')
        ->andReturn(['Source_Data' => []]);

    $loadService = new LoadService($psoClient, $scheduleService);

    $loadService->loadPSO(loadContext());
});

it('does not fetch schedule data when sendToPso is false, even with keepPsoData true', function () {
    $psoClient = mockedPsoClient();

    $scheduleService = Mockery::mock(ScheduleService::class);
    $scheduleService->shouldNotReceive('getScheduleData');

    $loadService = new LoadService($psoClient, $scheduleService);

    $loadService->loadPSO(loadContext(['sendToPso' => false]));
});

it('omits Broadcast and Broadcast_Parameter when no broadcasts are given', function () {
    $capturedPayload = [];
    $psoClient = mockedPsoClientCapturingPayload($capturedPayload);
    $scheduleService = Mockery::mock(ScheduleService::class);

    $loadService = new LoadService($psoClient, $scheduleService);
    $loadService->loadPSO(loadContext(['sendToPso' => false], ['keepPsoData' => false]));

    expect($capturedPayload)->not->toHaveKeys(['Broadcast', 'Broadcast_Parameter']);
});

it('builds a single Broadcast object (not a list) when one broadcast is given', function () {
    $capturedPayload = [];
    $psoClient = mockedPsoClientCapturingPayload($capturedPayload);
    $scheduleService = Mockery::mock(ScheduleService::class);

    $loadService = new LoadService($psoClient, $scheduleService);
    $loadService->loadPSO(loadContext(['sendToPso' => false], [
        'keepPsoData' => false,
        'broadcasts' => [
            [
                'broadcastTypeId' => 'REST',
                'planType' => 'COMPLETE',
                'parameters' => [
                    ['name' => 'mediatype', 'value' => 'application/json'],
                    ['name' => 'url', 'value' => 'https://example.com/callback'],
                ],
            ],
        ],
    ]));

    expect($capturedPayload['Broadcast'])
        ->toBeArray()
        ->and(array_is_list($capturedPayload['Broadcast']))->toBeFalse()
        ->and($capturedPayload['Broadcast']['broadcast_type_id'])->toBe('REST')
        ->and($capturedPayload['Broadcast_Parameter'])->toHaveCount(2);
});

it('converts maximumFrequency/maximumWait from minutes to an ISO 8601 duration', function () {
    $capturedPayload = [];
    $psoClient = mockedPsoClientCapturingPayload($capturedPayload);
    $scheduleService = Mockery::mock(ScheduleService::class);

    $loadService = new LoadService($psoClient, $scheduleService);
    $loadService->loadPSO(loadContext(['sendToPso' => false], [
        'keepPsoData' => false,
        'broadcasts' => [
            [
                'broadcastTypeId' => 'REST',
                'planType' => 'COMPLETE',
                'maximumFrequency' => 5,
                'maximumWait' => 90,
                'parameters' => [
                    ['name' => 'mediatype', 'value' => 'application/json'],
                    ['name' => 'url', 'value' => 'https://example.com/callback'],
                ],
            ],
        ],
    ]));

    expect($capturedPayload['Broadcast']['maximum_frequency'])->toBe('PT5M');
    expect($capturedPayload['Broadcast']['maximum_wait'])->toBe('PT1H30M');
});

it('builds a list of Broadcast objects when multiple broadcasts are given', function () {
    $capturedPayload = [];
    $psoClient = mockedPsoClientCapturingPayload($capturedPayload);
    $scheduleService = Mockery::mock(ScheduleService::class);

    $loadService = new LoadService($psoClient, $scheduleService);
    $loadService->loadPSO(loadContext(['sendToPso' => false], [
        'keepPsoData' => false,
        'broadcasts' => [
            [
                'broadcastTypeId' => 'REST',
                'planType' => 'COMPLETE',
                'parameters' => [
                    ['name' => 'mediatype', 'value' => 'application/json'],
                    ['name' => 'url', 'value' => 'https://example.com/callback'],
                ],
            ],
            [
                'broadcastTypeId' => 'EMAIL',
                'planType' => 'COMPLETE',
                'parameters' => [
                    ['name' => 'to_address', 'value' => 'ops@example.com'],
                    ['name' => 'smtp_server', 'value' => 'mail.example.com'],
                ],
            ],
        ],
    ]));

    expect($capturedPayload['Broadcast'])
        ->toHaveCount(2)
        ->and(array_is_list($capturedPayload['Broadcast']))->toBeTrue()
        ->and($capturedPayload['Broadcast_Parameter'])->toHaveCount(4);
});
