<?php

function validLoadPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'dseDuration' => 120,
            'processType' => 'DYNAMIC',
        ],
    ], $overrides);
}

it('returns a dry-run payload without contacting PSO when sendToPso is false', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload());

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
                            'process_type',
                            'duration',
                        ],
                    ],
                ],
            ],
        ]);

    expect($response->json('data.payloadToPso.dsScheduleData.Input_Reference.dataset_id'))
        ->toBe('dataset_123');
});

it('uses the client-supplied data.id as the Input_Reference id', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload(['data' => ['id' => 'load-123']]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData.Input_Reference.id'))->toBe('load-123');
});

it('requires dseDuration', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload(['data' => ['dseDuration' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.dseDuration');
});

it('requires a valid processType', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload(['data' => ['processType' => 'NOT_REAL']]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.processType');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

it('rejects a production baseUrl', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload([
        'environment' => ['baseUrl' => 'https://enercare-pso-prod.ifs.cloud'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('environment.baseUrl');
});

it('rejects a REST broadcast missing its required mediatype/url parameters', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload([
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
    $response = $this->postJson('/api/v2/load', validLoadPayload([
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
    $response = $this->postJson('/api/v2/load', validLoadPayload([
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
        ->and($broadcast['broadcast_type_id'])->toBe('REST')
        ->and($broadcast['plan_type'])->toBe('COMPLETE');

    expect($response->json('data.payloadToPso.dsScheduleData.Broadcast_Parameter'))->toHaveCount(2);
});

it('defaults a broadcast inputReferenceId to the payload own Input_Reference id', function () {
    $response = $this->postJson('/api/v2/load', validLoadPayload([
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
    $response = $this->postJson('/api/v2/load', validLoadPayload([
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
