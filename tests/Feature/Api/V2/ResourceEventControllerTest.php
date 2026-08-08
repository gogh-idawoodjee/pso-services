<?php

function validEventPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'eventType' => 'AO',
        ],
    ], $overrides);
}

it('creates a resource event using the resourceId from the route', function () {
    $response = $this->postJson('/api/v2/resource/RES-001/event', validEventPayload());

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Schedule_Event' => ['id', 'event_type_id', 'resource_id', 'event_date_time'],
                    ],
                ],
            ],
        ]);

    $event = $response->json('data.payloadToPso.dsScheduleData.Schedule_Event');
    expect($event['resource_id'])->toBe('RES-001')
        ->and($event['event_type_id'])->toBe('AO');
});

it('includes lat/long for a GPS fix event', function () {
    $response = $this->postJson('/api/v2/resource/RES-001/event', validEventPayload([
        'data' => ['eventType' => 'FIX', 'lat' => 43.6511, 'long' => -79.3470],
    ]));

    $response->assertStatus(202);

    $event = $response->json('data.payloadToPso.dsScheduleData.Schedule_Event');
    expect($event['latitude'])->toBe(43.6511)
        ->and($event['longitude'])->toBe(-79.347);
});

it('requires lat and long for a GPS fix event', function () {
    $response = $this->postJson('/api/v2/resource/RES-001/event', validEventPayload([
        'data' => ['eventType' => 'FIX'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors(['data.lat', 'data.long']);
});

it('requires a valid eventType', function () {
    $response = $this->postJson('/api/v2/resource/RES-001/event', validEventPayload([
        'data' => ['eventType' => 'NOT_A_TYPE'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.eventType');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/resource/RES-001/event', validEventPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});
