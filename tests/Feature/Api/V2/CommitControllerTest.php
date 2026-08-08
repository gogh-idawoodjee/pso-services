<?php

use Illuminate\Support\Facades\Http;

function suggestedDispatchBroadcast(array $overrides = []): array
{
    return array_replace_recursive([
        'Plan' => [
            ['dataset_id' => 'dataset_123'],
        ],
        'Suggested_Dispatch' => [
            'plan_id' => 'PLAN-1',
            'activity_id' => 'ACT-001',
            'resource_id' => 'RES-001',
            'visit_id' => 1,
            'fixed_resource' => true,
            'date_time_status' => '2025-05-17T08:00:00Z',
            'expected_start_datetime' => '2025-05-17T08:00:00',
            'expected_end_datetime' => '2025-05-17T09:00:00',
        ],
    ], $overrides);
}

beforeEach(function () {
    config([
        'pso-services.debug.base_url' => 'https://mycompany-pso-tst.ifs.cloud',
        'pso-services.debug.account_id' => 'Default',
        'pso-services.debug.username' => 'svc-user',
        'pso-services.debug.password' => 'svc-pass',
        'pso-services.settings.enable_commit_service_log' => false,
        'pso-services.settings.enable_debug' => false,
    ]);
});

it('commits a single suggested dispatch to PSO', function () {
    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'tok-123'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => ['Activity_Status' => []]], 200),
    ]);

    $response = $this->postJson('/api/v2/commit', suggestedDispatchBroadcast());

    $response->assertOk()
        ->assertJson(['status' => 200, 'message' => 'Successful. Sent to PSO']);

    $activityStatuses = $response->json('data.payloadToPso.dsScheduleData.Activity_Status');
    expect($activityStatuses)->toHaveCount(1)
        ->and($activityStatuses[0]['activity_id'])->toBe('ACT-001')
        ->and($activityStatuses[0]['status_id'])->toBe('30')
        ->and($activityStatuses[0]['resource_id'])->toBe('RES-001')
        ->and($activityStatuses[0]['duration'])->toBe('PT1H');

    Http::assertSent(fn($request) => str_contains($request->url(), 'scheduling/session')
        && $request['userName'] === 'svc-user'
        && $request['accountId'] === 'Default');

    Http::assertSent(fn($request) => str_contains($request->url(), 'scheduling/data')
        && $request->hasHeader('apiKey', 'tok-123'));
});

it('commits multiple suggested dispatches sharing one Input_Reference', function () {
    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'tok-123'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => ['Activity_Status' => []]], 200),
    ]);

    $response = $this->postJson('/api/v2/commit', [
        'Plan' => [['dataset_id' => 'dataset_123']],
        'Suggested_Dispatch' => [
            ['plan_id' => 'PLAN-1', 'activity_id' => 'ACT-001', 'resource_id' => 'RES-001', 'visit_id' => 1, 'fixed_resource' => true, 'date_time_status' => '2025-05-17T08:00:00Z', 'expected_start_datetime' => '2025-05-17T08:00:00', 'expected_end_datetime' => '2025-05-17T09:00:00'],
            ['plan_id' => 'PLAN-1', 'activity_id' => 'ACT-002', 'resource_id' => 'RES-002', 'visit_id' => 2, 'fixed_resource' => false, 'date_time_status' => '2025-05-17T08:00:00Z', 'expected_start_datetime' => '2025-05-17T09:00:00', 'expected_end_datetime' => '2025-05-17T09:30:00'],
        ],
    ]);

    $response->assertOk();

    $data = $response->json('data.payloadToPso.dsScheduleData');
    expect($data['Activity_Status'])->toHaveCount(2)
        ->and($data['Input_Reference']['description'])->toBe('Committing 2 Activities based on the SDS');
});

it('returns a no-op response when there is no suggested dispatch', function () {
    $response = $this->postJson('/api/v2/commit', ['Plan' => [['dataset_id' => 'dataset_123']]]);

    $response->assertOk()
        ->assertJson(['status' => 200, 'message' => 'No suggested dispatch to commit', 'data' => []]);
});

it('bubbles up an auth failure instead of attempting the commit', function () {
    Http::fake([
        '*/scheduling/session*' => Http::response(['Message' => 'AUTHENTICATION_FAILED'], 400),
    ]);

    $response = $this->postJson('/api/v2/commit', suggestedDispatchBroadcast());

    $response->assertStatus(401);

    Http::assertNotSent(fn($request) => str_contains($request->url(), 'scheduling/data'));
});
