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
