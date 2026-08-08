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
