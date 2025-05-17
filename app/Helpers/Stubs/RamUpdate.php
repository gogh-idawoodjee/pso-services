<?php

namespace App\Helpers\Stubs;

use App\Constants\PSOConstants;
use App\Enums\InputMode;

class RamUpdate
{
    public static function make(
        string $datasetId,
        int    $psoApiVersion = 1
    ): array
    {
        return [
            'organisation_id' => '2',
            'dataset_id' => $datasetId,
            'user_id' => PSOConstants::ARP_SOURCE_DATATYPE,
            'ram_update_type_id' => InputMode::CHANGE->value,
            'is_master_data' => true,
            'description' => 'Updating Shift from Ish Services',
            'requesting_app_instance_id' => PSOConstants::APP_INSTANCE_ID
        ];
    }
}
