<?php

use App\Classes\V2\PsoClient;
use App\DataTransferObjects\PsoContext;
use App\Services\V2\CleanupService;
use Illuminate\Http\JsonResponse;

function cleanupContext(array $dataOverrides = []): PsoContext
{
    return new PsoContext('tok-123', [
        'environment' => [
            'sendToPso' => true,
            'baseUrl' => 'https://example.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'account_001',
        ],
        'data' => $dataOverrides,
    ]);
}

it('reports nothing to clean up when there are no allocations', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')
        ->once()
        ->andReturn(new JsonResponse(['dsScheduleData' => []], 200));
    $psoClient->shouldNotReceive('sendToPso');

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext());

    expect($response->getData(true))
        ->toMatchArray(['status' => 200, 'data' => ['cleanupSummary' => ['activitiesDeleted' => 0, 'activityIds' => []]]]);
});

it('reports nothing to clean up when no allocation has expired', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')->once()->andReturn(new JsonResponse([
        'dsScheduleData' => [
            'Allocation' => [
                ['activity_id' => 'ACT-001', 'activity_end' => now()->addDay()->toAtomString()],
            ],
        ],
    ], 200));
    $psoClient->shouldNotReceive('sendToPso');

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext());

    expect($response->getData(true)['data']['cleanupSummary']['activitiesDeleted'])->toBe(0);
});

it('deletes activities whose allocation ended at or before the cutoff, deduplicated', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')->once()->andReturn(new JsonResponse([
        'dsScheduleData' => [
            'Allocation' => [
                ['activity_id' => 'ACT-001', 'activity_end' => now()->subDay()->toAtomString()],
                ['activity_id' => 'ACT-001', 'activity_end' => now()->subHours(2)->toAtomString()],
                ['activity_id' => 'ACT-002', 'activity_end' => now()->subDay()->toAtomString()],
                ['activity_id' => 'ACT-003', 'activity_end' => now()->addDay()->toAtomString()],
            ],
        ],
    ], 200));

    $psoClient->shouldReceive('buildPayload')->once()->andReturnUsing(fn ($payload) => ['dsScheduleData' => $payload]);
    $psoClient->shouldReceive('sendToPso')
        ->once()
        ->withArgs(function ($payload) {
            $deletions = $payload['dsScheduleData']['Object_Deletion'];
            expect($deletions)->toHaveCount(2);
            expect(collect($deletions)->pluck('object_pk1')->all())->toBe(['ACT-001', 'ACT-002']);

            return true;
        })
        ->andReturn(new JsonResponse(['dsScheduleData' => []], 200));

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext());

    $summary = $response->getData(true)['data']['cleanupSummary'];
    expect($summary['activitiesDeleted'])->toBe(2);
    expect($summary['activityIds'])->toBe(['ACT-001', 'ACT-002']);
});

it('treats a single allocation object (not a list) as one allocation', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')->once()->andReturn(new JsonResponse([
        'dsScheduleData' => [
            'Allocation' => ['activity_id' => 'ACT-001', 'activity_end' => now()->subDay()->toAtomString()],
        ],
    ], 200));
    $psoClient->shouldReceive('buildPayload')->once()->andReturnUsing(fn ($payload) => ['dsScheduleData' => $payload]);
    $psoClient->shouldReceive('sendToPso')->once()->andReturn(new JsonResponse(['dsScheduleData' => []], 200));

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext());

    expect($response->getData(true)['data']['cleanupSummary']['activityIds'])->toBe(['ACT-001']);
});

it('honors a custom cutoffDatetime override', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')->once()->andReturn(new JsonResponse([
        'dsScheduleData' => [
            'Allocation' => [
                ['activity_id' => 'ACT-001', 'activity_end' => '2026-01-01T00:00:00Z'],
            ],
        ],
    ], 200));
    $psoClient->shouldNotReceive('sendToPso');

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext(['cutoffDatetime' => '2025-01-01T00:00:00Z']));

    expect($response->getData(true)['data']['cleanupSummary']['activitiesDeleted'])->toBe(0);
});

it('passes through a failed schedule read without attempting a delete', function () {
    $psoClient = Mockery::mock(PsoClient::class);
    $psoClient->shouldReceive('getPsoData')->once()->andReturn(new JsonResponse(['error' => 'boom'], 500));
    $psoClient->shouldNotReceive('sendToPso');

    $response = (new CleanupService($psoClient))->cleanupDataset(cleanupContext());

    expect($response->status())->toBe(500);
});
