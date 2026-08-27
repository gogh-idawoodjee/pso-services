<?php

use App\Models\V2\Environment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function suggestedDispatchBroadcast(array $overrides = []): array
{
    return array_replace_recursive([
        'Plan' => [
            ['dataset_id' => 'dataset_123'],
        ],
        'Suggested_Dispatch' => [
            'plan_id' => 'PLAN-1',
            'activity_id' => 'ACT-001',
            'resource_id' => 'RES-001',
            'visit_id' => 1,
            'fixed_resource' => true,
            'date_time_status' => '2025-05-17T08:00:00Z',
            'expected_start_datetime' => '2025-05-17T08:00:00',
            'expected_end_datetime' => '2025-05-17T09:00:00',
        ],
    ], $overrides);
}

/**
 * krang_db is an external database owned by pso-test-tools — pso-services has
 * no migration for it. This builds just enough of the real `environments`
 * table (see pso-test-tools' 2025_04_29_01_create_environments_table
 * migration, plus the commit_token column added for this feature) to test
 * against, on the sqlite :memory: connection phpunit.xml points krang_db at.
 */
function createTestEnvironment(array $overrides = []): Environment
{
    return Environment::create(array_replace([
        'name' => 'Test Environment',
        'account_id' => 'Default',
        'base_url' => 'https://mycompany-pso-tst.ifs.cloud',
        'username' => 'svc-user',
        'password' => 'svc-pass',
        'commit_token' => Str::random(40),
    ], $overrides));
}

beforeEach(function () {
    Schema::connection('krang_db')->dropIfExists('environments');
    Schema::connection('krang_db')->create('environments', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('slug');
        $table->string('account_id');
        $table->string('base_url');
        $table->string('description')->nullable();
        $table->longText('password');
        $table->string('username');
        $table->string('commit_token')->unique();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
    });

    config(['pso-services.settings.enable_commit_service_log' => false]);
});

afterEach(function () {
    Schema::connection('krang_db')->dropIfExists('environments');
});

it('commits a single suggested dispatch to PSO using the Environment resolved from commit_token', function () {
    $environment = createTestEnvironment();

    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'tok-123'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => ['Activity_Status' => []]], 200),
    ]);

    $response = $this->postJson('/api/v2/commit/'.$environment->commit_token, suggestedDispatchBroadcast());

    $response->assertOk()
        ->assertJson(['status' => 200, 'message' => 'Successful. Sent to PSO']);

    $activityStatuses = $response->json('data.payloadToPso.dsScheduleData.Activity_Status');
    expect($activityStatuses)->toHaveCount(1)
        ->and($activityStatuses[0]['activity_id'])->toBe('ACT-001')
        ->and($activityStatuses[0]['status_id'])->toBe('30')
        ->and($activityStatuses[0]['resource_id'])->toBe('RES-001')
        ->and($activityStatuses[0]['duration'])->toBe('PT1H');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'scheduling/session')
        && $request['userName'] === 'svc-user'
        && $request['accountId'] === 'Default');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'mycompany-pso-tst.ifs.cloud')
        && str_contains($request->url(), 'scheduling/data')
        && $request->hasHeader('apiKey', 'tok-123'));
});

it('commits multiple suggested dispatches sharing one Input_Reference', function () {
    $environment = createTestEnvironment();

    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'tok-123'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => ['Activity_Status' => []]], 200),
    ]);

    $response = $this->postJson('/api/v2/commit/'.$environment->commit_token, [
        'Plan' => [['dataset_id' => 'dataset_123']],
        'Suggested_Dispatch' => [
            ['plan_id' => 'PLAN-1', 'activity_id' => 'ACT-001', 'resource_id' => 'RES-001', 'visit_id' => 1, 'fixed_resource' => true, 'date_time_status' => '2025-05-17T08:00:00Z', 'expected_start_datetime' => '2025-05-17T08:00:00', 'expected_end_datetime' => '2025-05-17T09:00:00'],
            ['plan_id' => 'PLAN-1', 'activity_id' => 'ACT-002', 'resource_id' => 'RES-002', 'visit_id' => 2, 'fixed_resource' => false, 'date_time_status' => '2025-05-17T08:00:00Z', 'expected_start_datetime' => '2025-05-17T09:00:00', 'expected_end_datetime' => '2025-05-17T09:30:00'],
        ],
    ]);

    $response->assertOk();

    $data = $response->json('data.payloadToPso.dsScheduleData');
    expect($data['Activity_Status'])->toHaveCount(2)
        ->and($data['Input_Reference']['description'])->toBe('Committing 2 Activities based on the SDS');
});

it('returns a no-op response when there is no suggested dispatch', function () {
    $environment = createTestEnvironment();

    $response = $this->postJson('/api/v2/commit/'.$environment->commit_token, ['Plan' => [['dataset_id' => 'dataset_123']]]);

    $response->assertOk()
        ->assertJson(['status' => 200, 'message' => 'No suggested dispatch to commit', 'data' => []]);
});

it('bubbles up an auth failure instead of attempting the commit', function () {
    $environment = createTestEnvironment();

    Http::fake([
        '*/scheduling/session*' => Http::response(['Message' => 'AUTHENTICATION_FAILED'], 400),
    ]);

    $response = $this->postJson('/api/v2/commit/'.$environment->commit_token, suggestedDispatchBroadcast());

    $response->assertStatus(401);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'scheduling/data'));
});

it('authenticates against the specific Environment matching the commit_token, not any other', function () {
    createTestEnvironment(['commit_token' => 'other-token', 'base_url' => 'https://other-env.ifs.cloud', 'username' => 'other-user']);
    $environment = createTestEnvironment(['commit_token' => 'the-real-token', 'base_url' => 'https://mycompany-pso-tst.ifs.cloud', 'username' => 'svc-user']);

    Http::fake([
        '*/scheduling/session*' => Http::response(['SessionToken' => 'tok-123'], 200),
        '*/scheduling/data*' => Http::response(['dsScheduleData' => ['Activity_Status' => []]], 200),
    ]);

    $this->postJson('/api/v2/commit/'.$environment->commit_token, suggestedDispatchBroadcast())
        ->assertOk();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'mycompany-pso-tst.ifs.cloud'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'other-env.ifs.cloud'));
});

it('returns 404 for an unknown commit_token', function () {
    $this->postJson('/api/v2/commit/does-not-exist', suggestedDispatchBroadcast())
        ->assertNotFound();
});
