<?php

namespace App\Helpers\Stubs;


use App\Enums\InputMode;
use App\Helpers\PSOHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;


class AppointmentRequest
{

    // so this badboy needs to make the appt request object plus the activity object and children
    // then send them both to the pso api
    public static function make(array $appointmentData, int $psoApiVersion = 1): array
    {

        $requestDateTime = data_get($appointmentData, 'data.inputDateTime') ?: Carbon::now()->startOfDay()->setTimezone('America/Toronto')->toAtomString();
        $appointmentRequest = [
            'id' => Str::orderedUuid()->getHex()->toString(),
            'slot_usage_rule_set_id' => data_get($appointmentData, 'data.slotUsageRuleId'),
            'appointment_template_id' => data_get($appointmentData, 'data.appointmentTemplateId'),
            // todo deal with customer timezones
            'appointment_base_datetime' => data_get($appointmentData, 'data.appointmentBaseDateTime') ?: Carbon::now()->startOfDay()->setTimezone('America/Toronto')->toIso8601String(),
            'appointment_template_duration' => PSOHelper::setPSODurationDays(data_get($appointmentData, 'data.appointmentTemplateDuration') ?? 21),
            'activity_id' => data_get($appointmentData, 'data.activityId') . config('pso-services.defaults.activity.appointment_booking_suffix'),
            'appointment_template_datetime' => data_get($appointmentData, 'data.appointmentTemplateDateTime'),
            'request_datetime' => $requestDateTime,
        ];

        $activity = Activity::make($appointmentData, true);
        $input_reference = InputReference::make(
            data_get($appointmentData, 'environment.datasetId'),
            InputMode::CHANGE,
            $requestDateTime,
            null,
            null,
            null,
            null,
            'Appointment Request for: ' . data_get($appointmentData, 'data.activityId')
        );

        return collect(['Appointment_Request' => $appointmentRequest])->merge($activity)->merge(['Input_Reference' => $input_reference])->toArray();

    }
}
