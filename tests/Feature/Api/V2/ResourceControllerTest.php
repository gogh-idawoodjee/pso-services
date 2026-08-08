<?php

use Illuminate\Support\Facades\Http;

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
