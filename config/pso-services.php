<?php

return [

    'statuses' => [
        'statuses_greater_than_alloc' => \App\Enums\ActivityStatus::statusesGreaterThanAllocated(),
        'all' => \App\Enums\ActivityStatus::allStatuses(),
        'commit_status' => \App\Enums\ActivityStatus::COMMITTED->value
    ],

    'defaults' => [
        'activity' => [
            'base_value' => env('DEFAULT_BASE_VALUE', 2000),
            'priority' => env('DEFAULT_PRIORITY', 1),
            'appointment_template_duration' => env('APPOINTMENT_TEMPLATE_DURATION', 21),
            'class_id' => 'CALL',
            'split_allowed' => false,
            'appointment_booking_suffix' => ''
        ],
        'resource' => [
            'class_id' => 'PERSON'
        ],
        'process_type' => env('DEFAULT_PROCESS_TYPE', 'APPOINTMENT'),
        'timeout' => env('DEFAULT_TIMEOUT', 5),
        'do_on_location_incentive' => 1,
        'do_in_locality_incentive' => 1,
        'timezone' => 'America/Toronto',
        'travel_broadcast_api' => 'http://pso-services-6g6mj.kinsta.app/api/travelanalyzerservice'

    ],

    'debug' => [
        'webhook_uuid' => '55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3',
        'base_url' => env('BASE_URL', 'https://pso.thetechnodro.me'),
        'username' => env('PSO_USERNAME'),
        'password' => env('PSO_PASSWORD'),
        'dataset_id' => env('DATASET_ID', 'NORTH'),
        'account_id' => env('ACCOUNT_ID', 'Default'),
        'debug_mode_on' => true,
        'debug_timeout' => 5
    ],
    'settings' => [
        'validate_object_existence' => true,
        'enable_commit_service_log' => true,
        'enable_swb_response_service_log' => true,
        'service_name' => 'Ish PSO Services',
        // used only in the USAGE output
        'use_system_date_format' => false,
        // if true, adds date_time_fixed during commit
        'fix_committed_activities' => true,
        // should be true if your input_datetime is in the past; see PSOActivityStatus class
        'override_commit_timestamps' => false,
        'override_commit_timestamp_value' => '2024-05-17T08:00:08+00:00',
        'enable_debug' => false,
        // if true, appointments cannot be accepted until they appointed first
        'force_appointed_check' => false,
        'use_region_as_locality' => true,
        'google_key' => env('GOOGLE_MAPS_API_KEY'),
        'shared_encryption_key' => env('SHREDDER_KEY'),
    ]
];
