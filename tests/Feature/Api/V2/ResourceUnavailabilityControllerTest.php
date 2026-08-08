<?php

function validUnavailabilityPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'resourceId' => 'RES-001',
            'categoryId' => 'VACATION',
            'duration' => 480,
            'baseDateTime' => '2025-05-10T08:00',
        ],
    ], $overrides);
}

it('creates a private schedule activity for a non-ARP unavailability', function () {
    $response = $this->postJson('/api/v2/resource/unavailability', validUnavailabilityPayload());

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Activity' => ['id', 'activity_class_id'],
                        'Activity_Status' => ['activity_id', 'status_id', 'resource_id'],
                    ],
                ],
            ],
        ]);

    $activity = $response->json('data.payloadToPso.dsScheduleData');
    expect($activity['Activity_Status']['resource_id'])->toBe('RES-001');
});

it('creates a RAM unavailability for an ARP unavailability', function () {
    $response = $this->postJson('/api/v2/resource/unavailability', validUnavailabilityPayload([
        'data' => ['isArpObject' => true, 'rotaId' => 'ROTA-001'],
    ]));

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'RAM_Update' => ['dataset_id', 'description'],
                        'RAM_Unavailability' => ['ram_resource_id', 'ram_unavailability_category_id', 'ram_time_pattern_id'],
                        'RAM_Time_Pattern' => ['id', 'base_time', 'duration'],
                    ],
                ],
            ],
        ]);

    $ram = $response->json('data.payloadToPso.dsScheduleData');
    expect($ram['RAM_Unavailability']['ram_resource_id'])->toBe('RES-001')
        ->and($ram['RAM_Time_Pattern']['duration'])->toBe('PT8H')
        ->and($ram['RAM_Unavailability']['ram_time_pattern_id'])->toBe($ram['RAM_Time_Pattern']['id']);
});

it('rejects an ARP unavailability without a rotaId', function () {
    $response = $this->postJson('/api/v2/resource/unavailability', validUnavailabilityPayload([
        'data' => ['isArpObject' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.rotaId');
});

it('updates a single ARP unavailability using the route id', function () {
    $response = $this->patchJson('/api/v2/resource/unavailability/UNAVAIL-001', validUnavailabilityPayload([
        'data' => ['isArpObject' => true, 'rotaId' => 'ROTA-001'],
    ]));

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'RAM_Update' => ['dataset_id', 'description'],
                        'RAM_Unavailability' => ['ram_resource_id', 'ram_unavailability_category_id', 'ram_time_pattern_id'],
                        'RAM_Time_Pattern' => ['id', 'base_time', 'duration'],
                    ],
                ],
            ],
        ]);

    $ram = $response->json('data.payloadToPso.dsScheduleData');
    expect($ram['RAM_Update']['description'])->toBe('Update Unavailability via Ish Services');
});

it('mass-updates several ARP unavailabilities sharing one time pattern', function () {
    $response = $this->patchJson('/api/v2/resource/unavailability/UNAVAIL-001', validUnavailabilityPayload([
        'data' => [
            'isArpObject' => true,
            'rotaId' => 'ROTA-001',
            'unavailabilityIds' => ['UNAVAIL-002', 'UNAVAIL-003'],
        ],
    ]));

    $response->assertStatus(202);

    $ram = $response->json('data.payloadToPso.dsScheduleData');
    expect($ram['RAM_Update']['description'])->toBe('Mass Update Unavailability via Ish Services')
        ->and($ram['RAM_Unavailability'])->toHaveCount(3)
        ->and(collect($ram['RAM_Unavailability'])->pluck('ram_time_pattern_id')->unique())->toHaveCount(1);
});

it('requires isArpObject to be true when updating', function () {
    $response = $this->patchJson('/api/v2/resource/unavailability/UNAVAIL-001', validUnavailabilityPayload([
        'data' => ['isArpObject' => false, 'rotaId' => 'ROTA-001'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.isArpObject');
});

it('requires isArpObject on update even when omitted', function () {
    $response = $this->patchJson('/api/v2/resource/unavailability/UNAVAIL-001', validUnavailabilityPayload([
        'data' => ['rotaId' => 'ROTA-001'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.isArpObject');
});
