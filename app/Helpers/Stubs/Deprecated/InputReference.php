<?php

namespace App\Helpers\Stubs\Deprecated;


use App\Enums\InputMode;
use App\Enums\ProcessType;
use Illuminate\Support\Str;

/**
 * @deprecated Use App\Builders\ActivityBuilder instead.
 * This class will be removed in an upcoming release.
 */
class InputReference
{

    // todo need to add source data
    public static function make(

        string           $datasetId,
        InputMode        $inputType = InputMode::LOAD,
        string|null      $datetime = null,
        string|null      $dseDuration = null,
        ProcessType|null $processType = null,
        string|null      $appointmentWindow = null,
        string|null      $id = null,
        string|null      $description = null,
        int              $psoApiVersion = 1,
    ): array
    {
        return array_filter([
            'datetime' => $datetime ?? now()->toAtomString(),
            'id' => $id ?? Str::orderedUuid()->getHex()->toString(),
            'input_type' => $inputType->value,
            'organisation_id' => '2',
            'dataset_id' => $datasetId,
            'user_id' => config('pso-services.settings.service_name'),
            'duration' => $dseDuration,
            'description' => $description,
            'process_type' => $processType?->value,
            'appointment_window_duration' => $appointmentWindow,
        ], static fn ($value) => $value !== null);
    }
}
