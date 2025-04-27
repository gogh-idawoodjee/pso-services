<?php

namespace App\Helpers\Stubs;


use App\Enums\InputMode;
use App\Enums\ProcessType;
use Carbon\Carbon;

use Illuminate\Support\Str;

class InputReference
{
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
        $inputReference = [
            'datetime' => $datetime ?? Carbon::now()->toAtomString(),
            'id' => $id ?? Str::orderedUuid()->getHex()->toString(),
            'input_type' => $inputType->value,
            'organisation_id' => '2',
            'dataset_id' => $datasetId,
            'user_id' => config('pso-services.settings.service_name'),
        ];

        if ($dseDuration) {
            $inputReference['duration'] = $dseDuration;
        }
        if ($description) {
            $inputReference['description'] = $description;
        }

        if ($processType) {
            $inputReference['process_type'] = $processType->value;
        }

        if ($appointmentWindow !== null) {
            $inputReference['appointment_window_duration'] = $appointmentWindow;
        }

        return $inputReference;
    }
}
