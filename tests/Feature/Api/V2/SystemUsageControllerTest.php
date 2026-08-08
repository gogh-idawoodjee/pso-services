<?php

use Illuminate\Support\Facades\Http;

function systemUsageHeaders(array $overrides = []): array
{
    return array_replace([
        'datasetId' => 'dataset_123',
        'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
        'accountId' => 'Default',
        'token' => 'tok-123',
    ], $overrides);
}

it('forwards the caller-supplied minDate and maxDate to PSO instead of defaulting to now', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);

    $this->getJson('/api/v2/usage?minDate=2020-01-01T00:00:00Z&maxDate=2020-01-02T00:00:00Z', systemUsageHeaders())
        ->assertOk();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'minimumDateTime=2020-01-01')
            && str_contains($request->url(), 'maximumDateTime=2020-01-02');
    });
});

it('requires the baseUrl header', function () {
    $headers = systemUsageHeaders();
    unset($headers['baseUrl']);

    $this->getJson('/api/v2/usage', $headers)
        ->assertStatus(422)
        ->assertJsonValidationErrors('baseUrl');
});

it('requires both minDate and maxDate together', function () {
    $this->getJson('/api/v2/usage?minDate=2020-01-01', systemUsageHeaders())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['minDate', 'maxDate']);
});
