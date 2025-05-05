<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\BaseFormRequest;
use Illuminate\Validation\Validator;

class ResourceShiftRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * Unique identifier for the shift.
             *
             * @scramble type string
             * @scramble required true
             * @scramble example "SHIFT-123"
             */
            'shiftId' => 'required|alpha_dash',

            /**
             * The dataset ID this shift belongs to.
             *
             * @scramble type string
             * @scramble required true
             * @scramble example "DEMO"
             */
            'datasetId' => 'required|string',

            /**
             * The rota ID (required only for ARP shifts).
             *
             * @scramble type string
             * @scramble required_if isArpShift=true
             * @scramble example "ROTA-001"
             */
            'rotaId' => 'string|required_if:isArpShift,true',

            /**
             * Indicates if this shift is using ARP format.
             *
             * @scramble type boolean
             * @scramble required false
             * @scramble example true
             */
            'isArpShift' => 'bool',

            /**
             * The shift type ID.
             * Required only if manual scheduling is turned on.
             *
             * @scramble type string
             * @scramble required_with turnManualSchedulingOn
             * @scramble example "SHT-TYPE-A"
             */
            'shiftType' => 'required_with:turnManualSchedulingOn|string',

            /**
             * Start datetime of the shift (ISO 8601).
             * Required only if `environment.sendToPso === false`.
             *
             * @scramble type string
             * @scramble format date-time
             * @scramble required false
             * @scramble example "2025-05-05T08:00:00Z"
             */
            'startDateTime' => 'date',

            /**
             * End datetime of the shift (ISO 8601).
             * Required only if `environment.sendToPso === false`.
             * Must be after `startDateTime`.
             *
             * @scramble type string
             * @scramble format date-time
             * @scramble required false
             * @scramble example "2025-05-05T16:00:00Z"
             */
            'endDateTime' => 'date|after:startDateTime',

            /**
             * Whether this shift should be forced into manual scheduling mode.
             *
             * @scramble type boolean
             * @scramble required false
             * @scramble example true
             */
            'turnManualSchedulingOn' => 'boolean',
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);
        $validator->sometimes('startDateTime', 'required', function () {
            return data_get($this->input('environment'), 'sendToPso') === false;
        });

        $validator->sometimes('endDateTime', 'required', function () {
            return data_get($this->input('environment'), 'sendToPso') === false;
        });
    }
}
