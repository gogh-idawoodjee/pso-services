<?php

function validShiftPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'shiftId' => 'SHIFT-001',
            'startDateTime' => '2025-05-29T08:00:00',
            'endDateTime' => '2025-05-29T17:00:00',
        ],
    ], $overrides);
}

it('updates a non-ARP shift using the resourceId from the route', function () {
    $response = $this->patchJson('/api/v2/resource/RES-001/shift', validShiftPayload());

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Shift' => ['id', 'resource_id', 'start_datetime', 'end_datetime'],
                    ],
                ],
            ],
        ]);

    $shift = $response->json('data.payloadToPso.dsScheduleData.Shift');
    expect($shift['resource_id'])->toBe('RES-001')
        ->and($shift['id'])->toBe('SHIFT-001');
});

it('updates an ARP shift as a RAM_Rota_Item', function () {
    $response = $this->patchJson('/api/v2/resource/RES-001/shift', validShiftPayload([
        'data' => ['isArpObject' => true, 'rotaId' => 'ROTA-001'],
    ]));

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'RAM_Rota_Item' => ['id', 'ram_resource_id', 'ram_rota_id'],
                    ],
                ],
            ],
        ]);

    $shift = $response->json('data.payloadToPso.dsScheduleData.RAM_Rota_Item');
    expect($shift['ram_resource_id'])->toBe('RES-001')
        ->and($shift['ram_rota_id'])->toBe('ROTA-001');
});

it('requires rotaId for an ARP shift', function () {
    $response = $this->patchJson('/api/v2/resource/RES-001/shift', validShiftPayload([
        'data' => ['isArpObject' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.rotaId');
});

it('requires shiftType when turning manual scheduling on', function () {
    $response = $this->patchJson('/api/v2/resource/RES-001/shift', validShiftPayload([
        'data' => ['turnManualSchedulingOn' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.shiftType');
});

it('requires startDateTime and endDateTime for a dry-run update', function () {
    $response = $this->patchJson('/api/v2/resource/RES-001/shift', validShiftPayload([
        'data' => ['startDateTime' => null, 'endDateTime' => null],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors(['data.startDateTime', 'data.endDateTime']);
});
