<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Validation\Validator;

class ResourceShiftRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->route('resourceId')) {
            $this->merge([
                'data' => array_merge((array) $this->input('data', []), [
                    'resourceId' => $this->route('resourceId'),
                ]),
            ]);
        }
    }

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The resource ID, taken from the route.
             * @var string
             * @example "RES-001"
             */
            'data.resourceId' => ['required', 'string'],

            /**
             * Unique identifier for the shift.
             * @var string
             * @example "SHIFT-123"
             */
            'data.shiftId' => 'required|alpha_dash',

            /**
             * The rota ID (required only for ARP shifts).
             * @var string|null
             * @example "ROTA-001"
             */
            'data.rotaId' => 'string|required_if:data.isArpObject,true',

            /**
             * Indicates if this shift is using ARP format.
             * @var bool|null
             * @example true
             */
            'data.isArpObject' => 'bool',

            /**
             * The shift type ID.
             * Required only if manual scheduling is turned on.
             * @var string|null
             * @example "SHT-TYPE-A"
             */
            'data.shiftType' => 'required_with:data.turnManualSchedulingOn|string',

            /**
             * Start datetime of the shift (ISO 8601).
             * Required only if `environment.sendToPso === false`.
             * @var string|null
             * @example "2025-05-05T08:00:00Z"
             */
            'data.startDateTime' => 'date',

            /**
             * End datetime of the shift (ISO 8601).
             * Required only if `environment.sendToPso === false`.
             * Must be after `startDateTime`.
             * @var string|null
             * @example "2025-05-05T16:00:00Z"
             */
            'data.endDateTime' => 'date|after:data.startDateTime',

            /**
             * Whether this shift should be forced into manual scheduling mode.
             * @var bool|null
             * @example true
             */
            'data.turnManualSchedulingOn' => 'boolean',
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);
        $validator->sometimes('data.startDateTime', 'required', function () {
            return data_get($this->input('environment'), 'sendToPso') === false;
        });

        $validator->sometimes('data.endDateTime', 'required', function () {
            return data_get($this->input('environment'), 'sendToPso') === false;
        });
    }

    public function bodyParameters(): array
    {
        return array_merge($this->commonBodyParameters(), [
            'data.resourceId' => [
                'description' => 'The resource ID, taken from the route.',
                'example' => 'RES-001',
            ],
            'data.shiftId' => [
                'description' => 'Unique identifier for the shift.',
                'example' => 'SHIFT-123',
            ],
            'data.rotaId' => [
                'description' => 'The rota ID. Required only for ARP shifts.',
                'example' => 'ROTA-001',
            ],
            'data.isArpObject' => [
                'description' => 'Indicates if this shift is using ARP format.',
                'example' => false,
            ],
            'data.shiftType' => [
                'description' => 'The shift type ID. Required only if manual scheduling is turned on.',
                'example' => 'SHT-TYPE-A',
            ],
            'data.startDateTime' => [
                'description' => 'Start datetime of the shift (ISO 8601). Required only if environment.sendToPso is false.',
                'example' => '2025-05-05T08:00:00Z',
            ],
            'data.endDateTime' => [
                'description' => 'End datetime of the shift (ISO 8601). Required only if environment.sendToPso is false. Must be after startDateTime.',
                'example' => '2025-05-05T16:00:00Z',
            ],
            'data.turnManualSchedulingOn' => [
                'description' => 'Whether this shift should be forced into manual scheduling mode.',
                'example' => false,
            ],
        ]);
    }
}
