<?php

use Illuminate\Support\Facades\Http;

function healthCheckPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => true,
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'datasetId' => 'dataset_123',
            'accountId' => 'Default',
            'username' => 'svc_user',
            'password' => 'secret',
        ],
    ], $overrides);
}

it('reports healthy when PSO accepts the credentials', function () {
    Http::fake([
        '*IFSSchedulingRESTfulGateway/api/v1/scheduling/session*' => Http::response(['SessionToken' => 'abc-123'], 200),
    ]);

    $response = $this->postJson('/api/v2/health-check', healthCheckPayload());

    $response->assertOk()->assertExactJson([
        'data' => ['status' => 'healthy'],
        'status' => 200,
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session'));
});

it('reports unauthorized when PSO rejects the credentials', function () {
    Http::fake([
        '*IFSSchedulingRESTfulGateway/api/v1/scheduling/session*' => Http::response(['Message' => 'AUTHENTICATION_FAILED'], 400),
    ]);

    $response = $this->postJson('/api/v2/health-check', healthCheckPayload());

    $response->assertStatus(401)->assertJson([
        'error' => 'Unauthorized. Please check your session or login credentials.',
    ]);
});

it('requires a token or credentials when sendToPso is true', function () {
    $response = $this->postJson('/api/v2/health-check', healthCheckPayload([
        'environment' => ['username' => null, 'password' => null],
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors('authentication');
});

it('does not contact PSO when sendToPso is false', function () {
    Http::fake();

    $response = $this->postJson('/api/v2/health-check', healthCheckPayload(['environment' => ['sendToPso' => false]]));

    $response->assertOk()->assertExactJson([
        'data' => ['status' => 'healthy'],
        'status' => 200,
    ]);

    Http::assertNothingSent();
});
