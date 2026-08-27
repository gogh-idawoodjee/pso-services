<?php

function validAppointmentPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'activityId' => 'ACT-001',
            'activityTypeId' => 'SERVICE_CALL',
            'duration' => 60,
            'slaStart' => '2026-05-01T08:00:00',
            'slaEnd' => '2026-05-01T17:00:00',
            'slaTypeId' => 'STANDARD',
            'appointmentTemplateId' => 'apptemplate-001',
            'lat' => 43.65107,
            'long' => -79.347015,
        ],
    ], $overrides);
}

it('uses the dsScheduleData wrapper by default (psoApiVersion 1)', function () {
    $response = $this->postJson('/api/v2/appointment', validAppointmentPayload());

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData'))->not->toBeNull();
    expect($response->json('data.payloadToPso.ScheduleData'))->toBeNull();
});

it('uses the ScheduleData wrapper when psoApiVersion is 2', function () {
    $response = $this->postJson('/api/v2/appointment', validAppointmentPayload([
        'environment' => ['psoApiVersion' => 2],
    ]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.ScheduleData'))->not->toBeNull();
    expect($response->json('data.payloadToPso.dsScheduleData'))->toBeNull();
});
