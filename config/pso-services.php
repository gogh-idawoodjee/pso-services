<?php

use App\Enums\ActivityStatus;

return [

    /*
    |--------------------------------------------------------------------------
    | Activity Statuses
    |--------------------------------------------------------------------------
    |
    | Pre-computed status lists used for filtering and validation throughout
    | the application. Derived from the ActivityStatus enum.
    |
    */

    'statuses' => [
        'statuses_greater_than_alloc' => ActivityStatus::statusesGreaterThanAllocated(),
        'all' => ActivityStatus::allStatuses(),
        'commit_status' => ActivityStatus::COMMITTED->value,
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Default values for PSO entities. These are used as fallbacks when the
    | caller does not provide explicit values in the request payload.
    |
    */

    'defaults' => [
        'activity' => [
            'base_value' => env('DEFAULT_BASE_VALUE', 2000),
            'priority' => env('DEFAULT_PRIORITY', 1),
            'appointment_template_duration' => env('APPOINTMENT_TEMPLATE_DURATION', 21),
            'class_id' => 'CALL',
            'split_allowed' => false,
            'appointment_booking_suffix' => '',

            // Multiplier applied to an accepted appointment's base_value so it resists
            // displacement by newly-arriving activities. Overridable per-request via
            // data.acceptedValueMultiplier on the accept call.
            'accepted_value_multiplier' => (float) env('APPOINTMENT_ACCEPTED_VALUE_MULTIPLIER', 1.5),
        ],
        'resource' => [
            'class_id' => 'PERSON',
        ],
        'process_type' => env('DEFAULT_PROCESS_TYPE', 'APPOINTMENT'),
        'timeout' => env('DEFAULT_TIMEOUT', 5),
        'do_on_location_incentive' => 1,
        'do_in_locality_incentive' => 1,
        'timezone' => env('PSO_TIMEZONE', 'America/Toronto'),
        'travel_broadcast_timeout_minutes' => (int) env('TRAVEL_BROADCAST_TIMEOUT', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Feature flags and behavioral toggles that control how the services
    | process and send data to PSO.
    |
    */

    'settings' => [
        // Validate that PSO objects exist before attempting operations
        'validate_object_existence' => true,

        // Enable logging for commit and SWB response services
        'enable_commit_service_log' => true,
        'enable_swb_response_service_log' => true,

        // Service identifier sent to PSO in Input_Reference payloads
        'service_name' => env('PSO_SERVICE_NAME', 'Ish PSO Services'),

        // Organisation identifier sent to PSO in Input_Reference payloads
        'organisation_id' => env('PSO_ORGANISATION_ID', '2'),

        // Use the system date format in USAGE output instead of ISO 8601
        'use_system_date_format' => false,

        // Add date_time_fixed to activities during commit
        'fix_committed_activities' => true,

        // Override commit timestamps — useful when input_datetime is in the past
        'override_commit_timestamps' => false,
        'override_commit_timestamp_value' => env('OVERRIDE_COMMIT_TIMESTAMP'),

        // Require appointed check before accepting appointments
        'force_appointed_check' => false,

        'use_region_as_locality' => true,
        'google_key' => env('GOOGLE_MAPS_API_KEY'),

        // Passthrough keyword: when sent as the Google API key in a request,
        // the server's configured google_key is used instead
        'google_api_passthrough' => env('GOOGLE_API_PASSTHROUGH'),
        'shared_encryption_key' => env('SHREDDER_KEY'),
    ],

];
