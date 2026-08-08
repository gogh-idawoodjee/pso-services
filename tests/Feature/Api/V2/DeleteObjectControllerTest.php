<?php

function validDeletePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'objectType' => 'Activity',
            'objectPk1' => 'ACT-001',
        ],
    ], $overrides);
}

it('returns a dry-run deletion payload without contacting PSO', function () {
    $response = $this->deleteJson('/api/v2/delete', validDeletePayload());

    $response->assertStatus(202)
        ->assertJson(['status' => 202, 'message' => 'Successful. Not sent to PSO by Request'])
        ->assertJsonStructure([
            'data' => [
                'payloadToPso' => [
                    'dsScheduleData' => [
                        'Object_Deletion',
                        'Input_Reference',
                    ],
                ],
            ],
        ]);
});

it('accepts the entity name as well as the label for objectType', function () {
    $response = $this->deleteJson('/api/v2/delete', validDeletePayload(['data' => ['objectType' => 'Activity']]));

    $response->assertStatus(202);
});

it('rejects an unknown objectType', function () {
    $response = $this->deleteJson('/api/v2/delete', validDeletePayload(['data' => ['objectType' => 'NotARealObject']]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.objectType');
});

it('requires the primary key attributes for the given objectType', function () {
    $response = $this->deleteJson('/api/v2/delete', validDeletePayload(['data' => ['objectPk1' => null]]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.objectPk1');
});

it('requires different primary key attributes for a composite-key object type', function () {
    $response = $this->deleteJson('/api/v2/delete', validDeletePayload([
        'data' => ['objectType' => 'Resource Region', 'objectPk1' => null],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors(['data.objectPk1', 'data.objectPk2']);
});
