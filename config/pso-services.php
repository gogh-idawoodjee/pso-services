<?php

return [

    'statuses' => [
        'statuses_greater_than_alloc' => [
            'travelling',
            'committed',
            'sent',
            'downloaded',
            'accepted',
            'waiting',
            'onsite',
            'pendingcompletion',
            'visitcomplete',
            'completed',
            'incomplete'
        ],
        'all' => [
            'travelling' => 50,
            'ignore' => -1,
            'committed' => 30,
            'sent' => 32,
            'unallocated' => 0,
            'downloaded' => 35,
            'accepted' => 40,
            'waiting' => 55,
            'onsite' => 60,
            'pendingcompletion' => 65,
            'visitcomplete' => 68,
            'completed' => 70,
            'incomplete' => 80
        ],
        'commit_status' => 30
    ],

    'defaults' => [
        'activity' => [
            'base_value' => env('DEFAULT_BASE_VALUE', 1000),
            'priority' => env('DEFAULT_PRIORITY', 1),
            'appointment_template_duration' => env('APPOINTMENT_TEMPLATE_DURATION', 7)
        ],
        'process_type' => env('DEFAULT_PROCESS_TYPE', 'APPOINTMENT'),
        'timeout' => env('DEFAULT_TIMEOUT', 5),
        'do_on_location_incentive' => 1.5,
        'do_in_locality_incentive' => 1.5

    ],

    'debug' => [
        'webhook_uuid' => '55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3',
        'base_url'      => env('BASE_URL', 'https://thetechnodro.me:950'),
        'username'      => env('PSO_USERNAME', 'admin'),
        'password'      => env('PSO_PASSWORD', 'Ohyouthinkdarknessisyourally1!'),
        'dataset_id'    => env('DATASET_ID', 'NORTH'),
        'account_id'    => env('ACCOUNT_ID', 'Default'),
        'debug_mode_on' => true,
        'debug_timeout' => 30
    ],
    'settings' => [
        'validate_object_existence' => true,
        'enable_commit_service_log' => true,
        'service_name' => 'the thingy',
        // used only in the USAGE output
        'use_system_date_format' => false,
        // if true, adds date_time_fixed during commit
        'fix_committed_activities' => true,
        // should be true if your input_datetime is in the past; see PSOActivityStatus class
        'override_commit_timestamps' => false,
        'enable_debug' => false,
        // if true, appointments cannot be accepted until they appointed first
        'force_appointed_check' => true
    ]
];
