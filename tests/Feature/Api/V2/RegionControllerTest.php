<?php

function validRegionPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'regions' => ['NORTH'],
        ],
    ], $overrides);
}

it('returns a dry-run DsModelling payload without contacting PSO when sendToPso is false', function () {
    $response = $this->postJson('/api/v2/region', validRegionPayload());

    $response->assertStatus(202)
        ->assertJson([
            'status' => 202,
            'message' => 'Successful. Not sent to PSO by Request',
        ]);

    expect($response->json('data.payloadToPso.DsModelling.@xmlns'))
        ->toBe('http://360Scheduling.com/Schema/DsModelling.xsd');

    $division = $response->json('data.payloadToPso.DsModelling.RAM_Division');
    expect($division)->toBeArray()
        ->and($division['id'])->toBe('NORTH');

    expect($response->json('data.payloadToPso.DsModelling.RAM_Update.dataset_id'))->toBe('dataset_123');
});

it('builds a list of RAM_Division rows for multiple regions', function () {
    $response = $this->postJson('/api/v2/region', validRegionPayload([
        'data' => ['regions' => ['NORTH', 'SOUTH']],
    ]));

    $response->assertStatus(202);

    expect($response->json('data.payloadToPso.DsModelling.RAM_Division'))->toHaveCount(2);
});

it('requires at least one region', function () {
    $response = $this->postJson('/api/v2/region', [
        'environment' => ['sendToPso' => false, 'datasetId' => 'dataset_123'],
        'data' => ['regions' => []],
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('data.regions');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/region', validRegionPayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

it('rejects a production baseUrl', function () {
    $response = $this->postJson('/api/v2/region', validRegionPayload([
        'environment' => ['baseUrl' => 'https://enercare-pso-prod.ifs.cloud'],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('environment.baseUrl');
});
