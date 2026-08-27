<?php

function validUpdateRotaPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'datetime' => '2026-01-01T00:00:00Z',
        ],
    ], $overrides);
}

it('returns a dry-run payload without contacting PSO when sendToPso is false', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload());

    $response->assertStatus(202)
        ->assertJson([
            'status' => 202,
            'message' => 'Successful. Not sent to PSO by Request',
        ])
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Input_Reference' => [
                            'input_type',
                            'dataset_id',
                        ],
                    ],
                ],
            ],
        ]);
});

it('uses the client-supplied data.id as the Input_Reference id', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload(['data' => ['id' => 'rota-123']]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Input_Reference.id'))->toBe('rota-123');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

it('rejects a REST broadcast missing its required mediatype/url parameters', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'data' => [
            'broadcasts' => [
                [
                    'broadcastTypeId' => 'REST',
                    'planType' => 'COMPLETE',
                    'parameters' => [
                        ['name' => 'httpmethod', 'value' => 'POST'],
                    ],
                ],
            ],
        ],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.broadcasts.0.parameters');
});

it('rejects an ADMIN plan_type broadcast missing application_type_id/check_in_expired_time', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'data' => [
            'broadcasts' => [
                [
                    'broadcastTypeId' => 'EMAIL',
                    'planType' => 'ADMIN',
                    'parameters' => [
                        ['name' => 'to_address', 'value' => 'ops@example.com'],
                        ['name' => 'smtp_server', 'value' => 'mail.example.com'],
                    ],
                ],
            ],
        ],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.broadcasts.0.parameters');
});

it('includes a single Broadcast object in the dry-run payload', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'data' => [
            'broadcasts' => [
                [
                    'broadcastTypeId' => 'REST',
                    'planType' => 'COMPLETE',
                    'parameters' => [
                        ['name' => 'mediatype', 'value' => 'application/json'],
                        ['name' => 'url', 'value' => 'https://example.com/callback'],
                    ],
                ],
            ],
        ],
    ]));

    $response->assertStatus(202);

    $broadcast = $response->json('data.payloadToPso.dsScheduleData.Broadcast');
    expect($broadcast)->toBeArray()
        ->and($broadcast['broadcast_type_id'])->toBe('REST');

    expect($response->json('data.payloadToPso.dsScheduleData.Broadcast_Parameter'))->toHaveCount(2);
});

it('defaults a broadcast inputReferenceId to the payload own Input_Reference id', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'data' => [
            'broadcasts' => [
                [
                    'broadcastTypeId' => 'REST',
                    'planType' => 'COMPLETE',
                    'parameters' => [
                        ['name' => 'mediatype', 'value' => 'application/json'],
                        ['name' => 'url', 'value' => 'https://example.com/callback'],
                    ],
                ],
            ],
        ],
    ]));

    $response->assertStatus(202);

    $inputReferenceId = $response->json('data.payloadToPso.dsScheduleData.Input_Reference.id');
    expect($inputReferenceId)->not->toBeEmpty();
    expect($response->json('data.payloadToPso.dsScheduleData.Broadcast.input_reference_id'))->toBe($inputReferenceId);
});

it('includes a list of Broadcast objects in the dry-run payload for multiple broadcasts', function () {
    $response = $this->patchJson('/api/v2/rota', validUpdateRotaPayload([
        'data' => [
            'broadcasts' => [
                [
                    'broadcastTypeId' => 'REST',
                    'planType' => 'COMPLETE',
                    'parameters' => [
                        ['name' => 'mediatype', 'value' => 'application/json'],
                        ['name' => 'url', 'value' => 'https://example.com/callback'],
                    ],
                ],
                [
                    'broadcastTypeId' => 'FILE',
                    'planType' => 'COMPLETE',
                    'parameters' => [
                        ['name' => 'file_path', 'value' => 'c:\\IFS\\Scheduling\\broadcast'],
                    ],
                ],
            ],
        ],
    ]));

    $response->assertStatus(202);

    expect($response->json('data.payloadToPso.dsScheduleData.Broadcast'))->toHaveCount(2);
    expect($response->json('data.payloadToPso.dsScheduleData.Broadcast_Parameter'))->toHaveCount(3);
});
