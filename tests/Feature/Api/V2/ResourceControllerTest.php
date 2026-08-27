<?php

use Illuminate\Support\Facades\Http;

function validResourceStorePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'resourceTypeId' => 'FIELD_TECH',
            'lat' => [43.65107],
            'long' => [-79.347015],
            'names' => ['John Smith'],
        ],
    ], $overrides);
}

it('returns a dry-run DsModelling payload for a single named resource', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload());

    $response->assertStatus(202)
        ->assertJson([
            'status' => 202,
            'message' => 'Successful. Not sent to PSO by Request',
        ]);

    $resource = $response->json('data.payloadToPso.DsModelling.RAM_Resource');
    expect($resource)->toBeArray()
        ->and($resource['id'])->toBe('JOHNSMITH')
        ->and($resource['first_name'])->toBe('John')
        ->and($resource['surname'])->toBe('Smith')
        ->and($resource['ram_resource_type_id'])->toBe('FIELD_TECH');

    $location = $response->json('data.payloadToPso.DsModelling.RAM_Location');
    expect($location['id'])->toBe('JOHNSMITH')
        ->and($location['latitude'])->toBe(43.65107);
});

it('builds a list of RAM_Resource/RAM_Location rows for multiple resources', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'data' => [
            'lat' => [43.65107, 43.70011],
            'long' => [-79.347015, -79.4163],
            'names' => ['John Smith', 'Jane Doe'],
        ],
    ]));

    $response->assertStatus(202);

    expect($response->json('data.payloadToPso.DsModelling.RAM_Resource'))->toHaveCount(2);
    expect($response->json('data.payloadToPso.DsModelling.RAM_Location'))->toHaveCount(2);
});

it('uses an explicit resource id when given', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'data' => ['ids' => ['RES-001']],
    ]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.DsModelling.RAM_Resource.id'))->toBe('RES-001');
});

it('includes RAM_Resource_Skill and RAM_Resource_Division when skills/regions are given', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'data' => ['skills' => ['ELECTRICAL'], 'regions' => ['NORTH']],
    ]));

    $response->assertStatus(202);

    $skill = $response->json('data.payloadToPso.DsModelling.RAM_Resource_Skill');
    expect($skill)->toBe(['ram_skill_id' => 'ELECTRICAL', 'ram_resource_id' => 'JOHNSMITH']);

    $division = $response->json('data.payloadToPso.DsModelling.RAM_Resource_Division');
    expect($division)->toBe(['ram_resource_id' => 'JOHNSMITH', 'ram_division_id' => 'NORTH']);
});

it('omits RAM_Resource_Skill and RAM_Resource_Division when none are given', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload());

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.DsModelling'))
        ->not->toHaveKeys(['RAM_Resource_Skill', 'RAM_Resource_Division']);
});

it('generates a random name for resources beyond the given names', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'data' => [
            'lat' => [43.65107, 43.70011],
            'long' => [-79.347015, -79.4163],
            'resourcesToCreate' => 2,
            'names' => ['John Smith'],
        ],
    ]));

    $response->assertStatus(202);

    $resources = $response->json('data.payloadToPso.DsModelling.RAM_Resource');
    expect($resources)->toHaveCount(2);
    expect($resources[0]['first_name'])->toBe('John');
    expect($resources[1]['first_name'])->not->toBeEmpty()->not->toBe('John');
});

it('requires resourceTypeId, lat, and long', function () {
    $response = $this->postJson('/api/v2/resource', [
        'environment' => ['sendToPso' => false, 'datasetId' => 'dataset_123'],
        'data' => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data.resourceTypeId', 'data.lat', 'data.long']);
});

it('rejects mismatched lat/long array lengths', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'data' => ['lat' => [1.0, 2.0], 'long' => [3.0]],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('data.long');
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/resource', validResourceStorePayload([
        'environment' => ['sendToPso' => true],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

function resourceHeaders(array $overrides = []): array
{
    return array_replace([
        'datasetId' => 'dataset_123',
        'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
        'accountId' => 'Default',
        'token' => 'tok-123',
    ], $overrides);
}

function fakeResourcePsoResponse(): array
{
    return [
        'dsScheduleData' => [
            'Resources' => [
                'id' => 'RES-001',
                'first_name' => 'John',
                'surname' => 'Smith',
                'resource_type_id' => 'TYPE-1',
                'location_id_start' => 'LOC-1',
                'location_id_end' => 'LOC-1',
            ],
            'Resource_Type' => [],
            'Additional_Attribute' => [],
            'Resource_Region' => [],
            'Region' => [],
            'Resource_Skill' => [],
            'Skill' => [],
            'Shift' => [],
            'Plan_Route' => [],
            'Location' => [],
        ],
    ];
}

it('queries PSO using the datasetId and baseUrl resolved from headers', function () {
    Http::fake(['*' => Http::response(fakeResourcePsoResponse(), 200)]);

    $this->getJson('/api/v2/resource/RES-001', resourceHeaders())
        ->assertOk()
        ->assertJsonPath('data.resource.resource_id', 'RES-001')
        ->assertJsonPath('data.resource.personal.full_name', 'John Smith');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mycompany-pso-tst.ifs.cloud')
            && str_contains($request->url(), 'datasetId=dataset_123')
            && str_contains($request->url(), 'resourceId=RES-001');
    });
});

it('returns 404 when PSO has no resource for the given id', function () {
    Http::fake(['*' => Http::response(['dsScheduleData' => []], 200)]);

    $this->getJson('/api/v2/resource/RES-404', resourceHeaders())
        ->assertNotFound();
});

it('requires the datasetId header', function () {
    $headers = resourceHeaders();
    unset($headers['datasetId']);

    $this->getJson('/api/v2/resource/RES-001', $headers)
        ->assertStatus(422)
        ->assertJsonValidationErrors('datasetId');
});

it('returns a map of resource id to display name for the whole dataset', function () {
    Http::fake(['*' => Http::response([
        'dsScheduleData' => [
            'Resources' => [
                ['id' => 'RES-001', 'first_name' => 'John', 'surname' => 'Smith'],
                ['id' => 'RES-002', 'first_name' => 'Jane', 'surname' => 'Doe'],
            ],
        ],
    ], 200)]);

    $this->getJson('/api/v2/resource', resourceHeaders())
        ->assertOk()
        ->assertJson(['data' => ['resources' => [
            'RES-001' => 'John Smith',
            'RES-002' => 'Jane Doe',
        ]]]);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'mycompany-pso-tst.ifs.cloud')
            && str_contains($request->url(), 'datasetId=dataset_123')
            && ! str_contains($request->url(), 'resourceId=');
    });
});

it('falls back to the resource id when a resource has no name', function () {
    Http::fake(['*' => Http::response([
        'dsScheduleData' => [
            'Resources' => [
                ['id' => 'RES-003', 'first_name' => '', 'surname' => ''],
            ],
        ],
    ], 200)]);

    $this->getJson('/api/v2/resource', resourceHeaders())
        ->assertOk()
        ->assertJson(['data' => ['resources' => ['RES-003' => 'RES-003']]]);
});

it('returns an empty map when the dataset has no resources', function () {
    Http::fake(['*' => Http::response(['dsScheduleData' => []], 200)]);

    $this->getJson('/api/v2/resource', resourceHeaders())
        ->assertOk()
        ->assertJson(['data' => ['resources' => []]]);
});

it('requires the datasetId header for the index route', function () {
    $headers = resourceHeaders();
    unset($headers['datasetId']);

    $this->getJson('/api/v2/resource', $headers)
        ->assertStatus(422)
        ->assertJsonValidationErrors('datasetId');
});
