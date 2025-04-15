<?php

namespace App\Helpers\Stubs;


use Carbon\Carbon;

use Illuminate\Support\Str;

class InputReference
{
    public static function make(
        string      $description,
        string      $inputType,
        string      $datasetId,
        string|null $datetime = null,
        string|null $dseDuration = null,
        string|null $processType = null,
        string|null $appointmentWindow = null,
        string|null $id = null,
    ): array
    {
        $inputReference = [
            'datetime' => $datetime ?? Carbon::now()->toAtomString(),
            'id' => $id ?? Str::orderedUuid()->getHex()->toString(),
            'description' => $description,
            'input_type' => strtoupper($inputType),
            'organisation_id' => '2',
            'dataset_id' => $datasetId,
            'user_id' => config('pso-services.settings.service_name'),
        ];

        if ($dseDuration) {
            $inputReference['duration'] = $dseDuration;
        }

        if ($processType) {
            $inputReference['process_type'] = strtoupper($processType);
        }

        if ($appointmentWindow !== null) {
            $inputReference['appointment_window_duration'] = $appointmentWindow;
        }

        return $inputReference;
    }
}
