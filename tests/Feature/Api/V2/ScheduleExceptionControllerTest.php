<?php

function validExceptionPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'exceptionTypeId' => 1,
            'label' => 'Sick Leave',
            'value' => '2025-05-17',
            'resourceId' => 'RES-001',
        ],
    ], $overrides);
}

it('creates a custom exception for a resource', function () {
    $response = $this->postJson('/api/v2/exception', validExceptionPayload());

    $response->assertStatus(202)
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Custom_Exception' => ['id', 'schedule_exception_type_id', 'resource_id'],
                        'Custom_Exception_Data' => ['custom_exception_id', 'label', 'sequence', 'value'],
                    ],
                ],
            ],
        ]);

    $data = $response->json('data.payloadToPso.dsScheduleData');
    expect($data['Custom_Exception']['resource_id'])->toBe('RES-001')
        ->and($data['Custom_Exception'])->not->toHaveKey('activity_id')
        ->and($data['Custom_Exception_Data']['custom_exception_id'])->toBe($data['Custom_Exception']['id'])
        ->and($data['Custom_Exception_Data']['label'])->toBe('Sick Leave');
});

it('creates a custom exception for an activity', function () {
    $response = $this->postJson('/api/v2/exception', validExceptionPayload([
        'data' => ['resourceId' => null, 'activityId' => 'ACT-001'],
    ]));

    $response->assertStatus(202);

    $exception = $response->json('data.payloadToPso.dsScheduleData.Custom_Exception');
    expect($exception['activity_id'])->toBe('ACT-001')
        ->and($exception)->not->toHaveKey('resource_id');
});

it('requires either activityId or resourceId', function () {
    $response = $this->postJson('/api/v2/exception', validExceptionPayload(['data' => ['resourceId' => null]]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data.activityId', 'data.resourceId']);
});

it('rejects both activityId and resourceId being provided', function () {
    $response = $this->postJson('/api/v2/exception', validExceptionPayload(['data' => ['activityId' => 'ACT-001']]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data.activityId', 'data.resourceId']);
});

it('requires exceptionTypeId, label, and value', function () {
    $response = $this->postJson('/api/v2/exception', validExceptionPayload([
        'data' => ['exceptionTypeId' => null, 'label' => null, 'value' => null],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data.exceptionTypeId', 'data.label', 'data.value']);
});
