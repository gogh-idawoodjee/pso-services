<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Support\Str;

class ActivityStoreRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->input('data.activityId')) {
            $this->merge([
                'data' => array_merge((array) $this->input('data', []), [
                    'activityId' => Str::orderedUuid()->getHex()->toString(),
                ]),
            ]);
        }
    }

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The activity ID. Auto-generated if not provided.
             * @var string
             * @example "act-123"
             */
            'data.activityId' => ['string'],

            /**
             * The PSO activity type ID.
             * @var string
             * @example "SERVICE_CALL"
             */
            'data.activityTypeId' => ['required', 'string'],

            /**
             * Duration of the activity, in minutes.
             * @var int
             * @example 60
             */
            'data.duration' => ['required', 'integer', 'gt:0'],

            /**
             * Latitude of the activity location.
             * @var float
             * @example 43.6511
             */
            'data.lat' => ['required', 'numeric', 'between:-90,90'],

            /**
             * Longitude of the activity location.
             * @var float
             * @example -79.3470
             */
            'data.long' => ['required', 'numeric', 'between:-180,180'],

            /**
             * Description of the activity.
             * @var string|null
             * @example "Instant Activity"
             */
            'data.description' => ['nullable', 'string'],

            /**
             * The SLA type ID.
             * @var string
             * @example "STANDARD"
             */
            'data.slaTypeId' => ['required', 'string'],

            /**
             * SLA start, ISO 8601.
             * @var string
             * @example "2025-04-30T14:30:00"
             */
            'data.slaStart' => ['required', 'date', 'before:data.slaEnd'],

            /**
             * SLA end, ISO 8601.
             * @var string
             * @example "2025-04-30T16:30:00"
             */
            'data.slaEnd' => ['required', 'date', 'after:data.slaStart'],

            /**
             * Priority of the activity.
             * @var int|null
             * @example 1
             */
            'data.priority' => ['nullable', 'integer'],

            /**
             * Base value of the activity.
             * @var float|null
             * @example 2000
             */
            'data.baseValue' => ['nullable', 'numeric'],

            /**
             * Skill IDs required for the activity.
             * @var string[]|null
             * @example ["ELECTRICAL"]
             */
            'data.skills' => ['nullable', 'array'],
            'data.skills.*' => ['string'],

            /**
             * Region IDs the activity belongs to.
             * @var string[]|null
             * @example ["REGION_1"]
             */
            'data.regions' => ['nullable', 'array'],
            'data.regions.*' => ['string'],
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
