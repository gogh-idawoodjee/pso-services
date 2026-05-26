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
        ],
        'resource' => [
            'class_id' => 'PERSON',
        ],
        'process_type' => env('DEFAULT_PROCESS_TYPE', 'APPOINTMENT'),
        'timeout' => env('DEFAULT_TIMEOUT', 5),
        'do_on_location_incentive' => 1,
        'do_in_locality_incentive' => 1,
        'timezone' => env('PSO_TIMEZONE', 'America/Toronto'),
        'travel_broadcast_api' => env('TRAVEL_BROADCAST_API', 'https://pso-services-6g6mj.kinsta.app/api/v2/travelanalyzerservice'),
        'travel_broadcast_timeout_minutes' => env('TRAVEL_BROADCAST_TIMEOUT', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug / Development
    |--------------------------------------------------------------------------
    |
    | Credentials and settings used by the scheduled rota task and for local
    | testing. These should NEVER be used in production request handling.
    | See issue #8 for planned migration to proper auth middleware.
    |
    */

    'debug' => [
        'webhook_uuid' => env('DEBUG_WEBHOOK_UUID', '55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3'),
        'base_url' => env('BASE_URL', 'https://pso.thetechnodro.me'),
        'username' => env('PSO_USERNAME'),
        'password' => env('PSO_PASSWORD'),
        'dataset_id' => env('DATASET_ID', 'NORTH'),
        'account_id' => env('ACCOUNT_ID', 'Default'),
        'debug_mode_on' => env('PSO_DEBUG_MODE', true),
        'debug_timeout' => env('PSO_DEBUG_TIMEOUT', 5),
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

        // Use the system date format in USAGE output instead of ISO 8601
        'use_system_date_format' => false,

        // Add date_time_fixed to activities during commit
        'fix_committed_activities' => true,

        // Override commit timestamps — useful when input_datetime is in the past
        'override_commit_timestamps' => false,
        'override_commit_timestamp_value' => env('OVERRIDE_COMMIT_TIMESTAMP'),

        'enable_debug' => env('PSO_ENABLE_DEBUG', false),

        // Require appointed check before accepting appointments
        'force_appointed_check' => false,

        'use_region_as_locality' => true,
        'google_key' => env('GOOGLE_MAPS_API_KEY'),
        'shared_encryption_key' => env('SHREDDER_KEY'),
    ],

];
