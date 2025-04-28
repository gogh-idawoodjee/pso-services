<?php

namespace App\Http\Requests\Api\V2;

class AppointmentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The ID of the activity.
             * @var string
             * @example "123e4567-e89b-12d3-a456-426614174000"
             */
            'data.activityId' => 'string|required',

            /**
             * The ID of the slot usage rule.
             * @var string
             * @example "usage-rule-01"
             */
            'data.slotUsageRuleId' => 'string',

            /**
             * List of skills associated with the appointment.
             * @var array
             * @example ["electrician", "plumber"]
             */
            'data.skills' => 'array',

            /**
             * List of regions associated with the appointment.
             * @var array
             * @example ["north", "south"]
             */
            'data.regions' => 'array',

            /**
             * The ID of the activity type.
             * @var string
             * @example "activity-type-01"
             */
            'data.activityTypeId' => 'string|required',

            /**
             * Duration of the appointment in minutes (must be less than 1440).
             * @var int
             * @example 120
             */
            'data.duration' => 'integer|lt:1440|required',

            /**
             * The base value for the appointment.
             * @var int
             * @example 100
             */
            'data.baseValue' => 'integer|gt:0',

            /**
             * The visit ID.
             * @var int
             * @example 987654
             */
            'data.visitId' => 'integer|gt:0',

            /**
             * Priority level of the appointment.
             * @var int
             * @example 5
             */
            'data.priority' => 'integer',

            /**
             * SLA start datetime in ISO 8601 format.
             * @var string
             * @example "2024-05-01T08:00:00"
             */
            'data.slaStart' => 'date_format:Y-m-d\TH:i:s|before:sla_end|required',

            /**
             * SLA end datetime in ISO 8601 format.
             * @var string
             * @example "2024-05-01T12:00:00"
             */
            'data.slaEnd' => 'date_format:Y-m-d\TH:i:s|after:sla_start|required',

            /**
             * The SLA type ID.
             * @var string
             * @example "sla-type-standard"
             */
            'data.slaTypeId' => 'string|required',

            /**
             * Appointment template ID.
             * @var string
             * @example "apptemplate-001"
             */
            'data.appointmentTemplateId' => 'string|required',

            /**
             * Duration of the appointment template.
             * @var int
             * @example 90
             */
            'data.appointmentTemplateDuration' => 'integer|gte:0',

            /**
             * Appointment template datetime.
             * @var string
             * @example "2024-05-01T10:00:00"
             */
            'data.appointmentTemplateDatetime' => 'date_format:Y-m-d\TH:i:s',

            /**
             * Appointment base datetime.
             * @var string
             * @example "2024-05-01T09:00:00"
             */
            'data.appointmentBaseDateTime' => 'date_format:Y-m-d\TH:i:s',

            /**
             * Input datetime.
             * @var string
             * @example "2024-05-01T09:30:00"
             */
            'data.inputDatetime' => 'date_format:Y-m-d\TH:i:s',

            /**
             * Latitude coordinate.
             * @var float
             * @example 43.65107
             */
            'data.lat' => 'numeric|between:-90,90|required',

            /**
             * Longitude coordinate.
             * @var float
             * @example -79.347015
             */
            'data.long' => 'numeric|between:-180,180|required',

            /**
             * The timezone identifier.
             * @var string
             * @example "America/Toronto"
             */
            'data.timezone' => 'timezone:all',

            /**
             * Whether appointment splitting is allowed.
             * @var boolean
             * @example true
             */
            'data.splitAllowed' => 'boolean',
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
