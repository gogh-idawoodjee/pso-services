<?php

use App\Enums\AppointmentRequestStatus;
use App\Http\Resources\AppointmentResource;
use App\Models\V2\PSOAppointment;
use Illuminate\Support\Str;

function appointmentRecordForResource(array $overrides = []): PSOAppointment
{
    return PSOAppointment::create(array_replace([
        'run_id' => (string) Str::uuid(),
        'short_code' => 'abc123',
        'appointment_request' => ['Appointment_Request' => ['id' => 'req-'.(string) Str::uuid()]],
        'service_api_input' => [],
        'appointment_request_id' => 'req-'.(string) Str::uuid(),
        'status' => AppointmentRequestStatus::UNACKNOWLEDGED->value,
        'activity' => [],
        'activity_id' => 'ACT-001_AB',
        'base_url' => 'https://mycompany-pso-tst.ifs.cloud',
        'dataset_id' => 'dataset_123',
        'input_reference_id' => 'ir-1',
        'input_request' => [],
        'appointment_template_id' => 'apptemplate-001',
        'appointment_template_duration' => 'P21D',
        'appointment_template_datetime' => now(),
        'total_offers_returned' => 2,
        'total_valid_offers_returned' => 1,
        'total_invalid_offers_returned' => 1,
        'offer_expiry_datetime' => now()->addMinutes(10),
    ], $overrides));
}

it('omits the response block entirely for a status that is neither accepted nor declined', function () {
    $appointment = appointmentRecordForResource(['status' => AppointmentRequestStatus::UNACKNOWLEDGED->value]);

    $offers = (new AppointmentResource($appointment))->toArray(request())['summary']['offers'];

    expect($offers)->not->toHaveKey('response');
});

it('includes only accepted-specific fields in the response block for an accepted appointment', function () {
    $appointment = appointmentRecordForResource([
        'status' => AppointmentRequestStatus::ACCEPTED->value,
        'accepted_offer_id' => 5,
        'accept_decline_datetime' => now(),
        'accept_decline_input_reference_id' => 'ir-accept',
    ]);

    $response = (new AppointmentResource($appointment))->toArray(request())['summary']['offers']['response'];

    expect($response)->toHaveKeys(['status', 'inputReferenceId', 'acceptedDateTime', 'acceptedOfferId', 'acceptedDelay'])
        ->and($response)->not->toHaveKeys(['declinedDateTime', 'declinedDelay']);
});

it('includes only declined-specific fields in the response block for a declined appointment', function () {
    $appointment = appointmentRecordForResource([
        'status' => AppointmentRequestStatus::DECLINED->value,
        'accept_decline_datetime' => now(),
        'accept_decline_input_reference_id' => 'ir-decline',
    ]);

    $response = (new AppointmentResource($appointment))->toArray(request())['summary']['offers']['response'];

    expect($response)->toHaveKeys(['status', 'inputReferenceId', 'declinedDateTime', 'declinedDelay'])
        ->and($response)->not->toHaveKeys(['acceptedOfferId', 'acceptedDelay', 'acceptedDateTime']);
});
