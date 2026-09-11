<?php

function validActivityPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'activityTypeId' => 'SERVICE_CALL',
            'duration' => 60,
            'lat' => 43.6511,
            'long' => -79.3470,
            'slaTypeId' => 'STANDARD',
            'slaStart' => '2025-04-30T14:30:00',
            'slaEnd' => '2025-04-30T16:30:00',
        ],
    ], $overrides);
}

it('returns a dry-run activity payload without contacting PSO', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload());

    $response->assertStatus(202)
        ->assertJson(['status' => 202, 'message' => 'Successful. Not sent to PSO by Request'])
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Activity' => ['id', 'activity_type_id', 'duration'],
                        'Activity_Status' => ['activity_id', 'status_id'],
                        'Location' => ['id'],
                        'Input_Reference',
                    ],
                ],
            ],
        ]);

    $activity = $response->json('data.payloadToPso.dsScheduleData');

    expect($activity['Activity_Status']['status_id'])->toBe('0')
        ->and($activity['Activity']['activity_type_id'])->toBe('SERVICE_CALL');
});

it('auto-generates an activityId when none is provided', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload());

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Activity.id'))->not->toBeEmpty();
});

it('uses the caller-supplied activityId when provided', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload(['data' => ['activityId' => 'act-999']]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Activity.id'))->toBe('act-999');
});

it('requires activityTypeId', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload(['data' => ['activityTypeId' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.activityTypeId');
});

it('requires duration', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload(['data' => ['duration' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.duration');
});

it('requires lat and long', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload(['data' => ['lat' => null, 'long' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors(['data.lat', 'data.long']);
});

it('requires slaTypeId, slaStart, and slaEnd', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload([
        'data' => ['slaTypeId' => null, 'slaStart' => null, 'slaEnd' => null],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors(['data.slaTypeId', 'data.slaStart', 'data.slaEnd']);
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

it('uses the client-supplied data.inputDatetime as the Input_Reference datetime', function () {
    $response = $this->postJson('/api/v2/activity', validActivityPayload([
        'data' => ['inputDatetime' => '2026-01-01T00:00:00Z'],
    ]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Input_Reference.datetime'))
        ->toBe('2026-01-01T00:00:00Z');
});

it('uses the client-supplied data.inputDatetime as the Input_Reference datetime when deleting activities', function () {
    $response = $this->deleteJson('/api/v2/activity', [
        'environment' => ['sendToPso' => false, 'datasetId' => 'dataset_123'],
        'data' => ['activities' => ['act-123'], 'inputDatetime' => '2026-01-01T00:00:00Z'],
    ]);

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Input_Reference.datetime'))
        ->toBe('2026-01-01T00:00:00Z');
});
