<?php

use App\Enums\AppointmentRequestStatus;
use App\Models\V2\PSOAppointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function validAppointmentPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'environment' => [
            'sendToPso' => false,
            'datasetId' => 'dataset_123',
        ],
        'data' => [
            'activityId' => 'ACT-001',
            'activityTypeId' => 'SERVICE_CALL',
            'duration' => 60,
            'slaStart' => '2026-05-01T08:00:00',
            'slaEnd' => '2026-05-01T17:00:00',
            'slaTypeId' => 'STANDARD',
            'appointmentTemplateId' => 'apptemplate-001',
            'lat' => 43.65107,
            'long' => -79.347015,
        ],
    ], $overrides);
}

it('uses the dsScheduleData wrapper by default (psoApiVersion 1)', function () {
    $response = $this->postJson('/api/v2/appointment', validAppointmentPayload());

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.dsScheduleData'))->not->toBeNull();
    expect($response->json('data.payloadToPso.ScheduleData'))->toBeNull();
});

it('uses the ScheduleData wrapper when psoApiVersion is 2', function () {
    $response = $this->postJson('/api/v2/appointment', validAppointmentPayload([
        'environment' => ['psoApiVersion' => 2],
    ]));

    $response->assertStatus(202);
    expect($response->json('data.payloadToPso.ScheduleData'))->not->toBeNull();
    expect($response->json('data.payloadToPso.dsScheduleData'))->toBeNull();
});

it('sends the slot usage rule to PSO as slot_usage_rule_set_id, not slot_usage_rule_id', function () {
    $response = $this->postJson('/api/v2/appointment', validAppointmentPayload([
        'data' => ['slotUsageRuleId' => 'usage-rule-01'],
    ]));

    $response->assertStatus(202);

    $appointmentRequest = $response->json('data.payloadToPso.dsScheduleData.Appointment_Request');
    expect($appointmentRequest['slot_usage_rule_set_id'])->toBe('usage-rule-01')
        ->and($appointmentRequest)->not->toHaveKey('slot_usage_rule_id');
});

function declinableAppointmentRecord(array $overrides = []): PSOAppointment
{
    $appointmentRequestId = 'req-'.(string) Str::uuid();

    return PSOAppointment::create(array_replace([
        'run_id' => (string) Str::uuid(),
        'short_code' => 'abc123',
        'appointment_request' => ['Appointment_Request' => ['id' => $appointmentRequestId]],
        'service_api_input' => ['baseUrl' => 'https://mycompany-pso-tst.ifs.cloud', 'datasetId' => 'dataset_123'],
        'appointment_request_id' => $appointmentRequestId,
        'status' => AppointmentRequestStatus::UNACKNOWLEDGED->value,
        'activity' => [],
        'activity_id' => 'ACT-001_AB',
        'base_url' => 'https://mycompany-pso-tst.ifs.cloud',
        'dataset_id' => 'dataset_123',
        'input_reference_id' => 'ir-1',
        'input_request' => [],
        'appointment_template_id' => 'apptemplate-001',
        'valid_offers' => [['id' => 1, 'offerValue' => '100']],
        'total_valid_offers_returned' => 1,
        'total_invalid_offers_returned' => 0,
        'offer_expiry_datetime' => now()->addMinutes(10),
    ], $overrides));
}

it('marks cleanup_datetime after declining, so the delayed cleanup job will not re-delete the activity', function () {
    Http::fake(['*' => Http::response(['dsScheduleData' => []], 200)]);

    $appointment = declinableAppointmentRecord();

    $this->deleteJson("/api/v2/appointment/{$appointment->appointment_request_id}", [
        'environment' => [
            'sendToPso' => true,
            'token' => 'tok-123',
            'datasetId' => 'dataset_123',
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'accountId' => 'Default',
        ],
    ])->assertOk();

    $appointment->refresh();
    expect($appointment->cleanup_datetime)->not->toBeNull();
});

it('raises base_value by the default multiplier when accepting an offer', function () {
    Http::fake(['*' => Http::response(['dsScheduleData' => []], 200)]);

    $appointment = declinableAppointmentRecord([
        'activity' => [
            'Activity' => ['id' => 'ACT-001_AB', 'base_value' => 2000],
            'Activity_Status' => ['activity_id' => 'ACT-001_AB'],
        ],
        'valid_offers' => [[
            'id' => 1,
            'windowStartDatetime' => '2026-05-01T08:00:00-04:00',
            'windowEndDatetime' => '2026-05-01T10:00:00-04:00',
            'prospectiveAllocationStart' => '2026-05-01T08:15:00-04:00',
            'prospectiveResourceId' => 'RES-001',
        ]],
    ]);

    $response = $this->patchJson("/api/v2/appointment/{$appointment->appointment_request_id}", [
        'data' => ['appointmentOfferId' => 1],
        'environment' => [
            'sendToPso' => true,
            'token' => 'tok-123',
            'datasetId' => 'dataset_123',
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'accountId' => 'Default',
        ],
    ]);

    $response->assertOk();
    expect($response->json('data.payloadToPso.payloadToPso.dsScheduleData.Activity.base_value'))->toBe(3000);
});

it('raises base_value by a caller-supplied multiplier when accepting an offer', function () {
    Http::fake(['*' => Http::response(['dsScheduleData' => []], 200)]);

    $appointment = declinableAppointmentRecord([
        'activity' => [
            'Activity' => ['id' => 'ACT-001_AB', 'base_value' => 1000],
            'Activity_Status' => ['activity_id' => 'ACT-001_AB'],
        ],
        'valid_offers' => [[
            'id' => 1,
            'windowStartDatetime' => '2026-05-01T08:00:00-04:00',
            'windowEndDatetime' => '2026-05-01T10:00:00-04:00',
            'prospectiveAllocationStart' => '2026-05-01T08:15:00-04:00',
            'prospectiveResourceId' => 'RES-001',
        ]],
    ]);

    $response = $this->patchJson("/api/v2/appointment/{$appointment->appointment_request_id}", [
        'data' => ['appointmentOfferId' => 1, 'acceptedValueMultiplier' => 3],
        'environment' => [
            'sendToPso' => true,
            'token' => 'tok-123',
            'datasetId' => 'dataset_123',
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'accountId' => 'Default',
        ],
    ]);

    $response->assertOk();
    expect($response->json('data.payloadToPso.payloadToPso.dsScheduleData.Activity.base_value'))->toBe(3000);
});

it('rejects an acceptedValueMultiplier of 1 or less', function () {
    $appointment = declinableAppointmentRecord();

    $this->patchJson("/api/v2/appointment/{$appointment->appointment_request_id}", [
        'data' => ['appointmentOfferId' => 1, 'acceptedValueMultiplier' => 1],
        'environment' => [
            'sendToPso' => true,
            'token' => 'tok-123',
            'datasetId' => 'dataset_123',
            'baseUrl' => 'https://mycompany-pso-tst.ifs.cloud',
            'accountId' => 'Default',
        ],
    ])->assertStatus(422)->assertJsonValidationErrors('data.acceptedValueMultiplier');
});
