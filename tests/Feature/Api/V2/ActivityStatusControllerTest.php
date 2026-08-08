<?php

function validActivityStatusPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'status' => 'allocated',
            'resourceId' => 'resource-123',
        ],
    ], $overrides);
}

it('returns a dry-run activity status payload without contacting PSO', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload());

    $response->assertStatus(202)
        ->assertJson(['status' => 202, 'message' => 'Successful. Not sent to PSO by Request'])
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Activity_Status' => ['activity_id', 'status_id', 'resource_id'],
                    ],
                ],
            ],
        ]);

    $activityStatus = $response->json('data.payloadToPso.dsScheduleData.Activity_Status');

    expect($activityStatus['activity_id'])->toBe('act-123')
        ->and($activityStatus['status_id'])->toBe('10')
        ->and($activityStatus['resource_id'])->toBe('resource-123');
});

it('accepts the numeric status value in place of the status name', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload(['data' => ['status' => '10']]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Activity_Status.status_id'))->toBe('10');
});

it('does not require a resourceId for statuses below allocated', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload([
        'data' => ['status' => 'unallocated', 'resourceId' => null],
    ]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Activity_Status.status_id'))->toBe('0');
});

it('requires status', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload(['data' => ['status' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.status');
});

it('rejects an invalid status value', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload(['data' => ['status' => 'not-a-real-status']]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.status');
});

it('requires resourceId for statuses at or above allocated', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload(['data' => ['resourceId' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.resourceId');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->patchJson('/api/v2/activity/act-123/status', validActivityStatusPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});
