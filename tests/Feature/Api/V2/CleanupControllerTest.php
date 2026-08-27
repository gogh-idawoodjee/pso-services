<?php

use Illuminate\Support\Facades\Http;

function cleanupPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'Default',
            'token' => 'tok-123',
        ],
        'data' => [],
    ], $overrides);
}

it('deletes expired activities and reports a summary', function () {
    Http::fake([
        '*/scheduling/data*' => Http::sequence()
            ->push([
                'dsScheduleData' => [
                    'Allocation' => [
                        ['activity_id' => 'ACT-001', 'activity_end' => now()->subDay()->toAtomString()],
                        ['activity_id' => 'ACT-002', 'activity_end' => now()->addDay()->toAtomString()],
                    ],
                ],
            ], 200)
            ->push(['dsScheduleData' => []], 200),
    ]);

    $response = $this->deleteJson('/api/v2/cleanup', cleanupPayload());

    $response->assertOk()
        ->assertJson([
            'status' => 200,
            'message' => 'Successful. Sent to PSO',
            'data' => ['cleanupSummary' => ['activitiesDeleted' => 1, 'activityIds' => ['ACT-001']]],
        ]);

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && str_contains($request->url(), 'scheduling/data')
        && str_contains($request->url(), 'includeOutput=true'));

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_contains($request->url(), 'scheduling/data')
        && $request['dsScheduleData']['Object_Deletion'][0]['object_pk1'] === 'ACT-001');
});

it('reports nothing to clean up without attempting a delete', function () {
    Http::fake([
        '*/scheduling/data*' => Http::response(['dsScheduleData' => []], 200),
    ]);

    $response = $this->deleteJson('/api/v2/cleanup', cleanupPayload());

    $response->assertOk()
        ->assertJson([
            'status' => 200,
            'message' => 'Nothing to clean up.',
            'data' => ['cleanupSummary' => ['activitiesDeleted' => 0, 'activityIds' => []]],
        ]);

    Http::assertSentCount(1);
});

it('exchanges username/password for a token when no token is supplied', function () {
    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'exchanged-tok'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => []], 200),
    ]);

    $response = $this->deleteJson('/api/v2/cleanup', cleanupPayload([
        'environment' => ['token' => null, 'username' => 'svc-user', 'password' => 'svc-pass'],
    ]));

    $response->assertOk();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'scheduling/session')
        && $request['userName'] === 'svc-user');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'scheduling/data')
        && $request->hasHeader('apiKey', 'exchanged-tok'));
});

it('requires baseUrl, accountId, and a token or credentials regardless of sendToPso', function () {
    $response = $this->deleteJson('/api/v2/cleanup', cleanupPayload([
        'environment' => ['baseUrl' => null, 'accountId' => null, 'token' => null],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['environment.baseUrl', 'environment.accountId', 'authentication']);
});

it('honors a custom cutoffDatetime', function () {
    Http::fake([
        '*/scheduling/data*' => Http::response([
            'dsScheduleData' => [
                'Allocation' => [
                    ['activity_id' => 'ACT-001', 'activity_end' => '2026-01-01T00:00:00Z'],
                ],
            ],
        ], 200),
    ]);

    $response = $this->deleteJson('/api/v2/cleanup', cleanupPayload([
        'data' => ['cutoffDatetime' => '2025-01-01T00:00:00Z'],
    ]));

    $response->assertOk()
        ->assertJson(['data' => ['cleanupSummary' => ['activitiesDeleted' => 0]]]);

    Http::assertSentCount(1);
});
