<?php

namespace App\Helpers\Stubs;

use App\Constants\PSOConstants;
use App\Enums\RamUpdateType;

class RamUpdate
{
    public static function make(
        string $datasetId,
        string $description,
    ): array
    {
        return [
            'organisation_id' => config('pso-services.settings.organisation_id'),
            'dataset_id' => $datasetId,
            'user_id' => PSOConstants::ARP_SOURCE_DATATYPE,
            'ram_update_type_id' => RamUpdateType::CHANGE->value,
            'is_master_data' => true,
            'description' => $description,
            'requesting_app_instance_id' => PSOConstants::APP_INSTANCE_ID
        ];
    }
}
