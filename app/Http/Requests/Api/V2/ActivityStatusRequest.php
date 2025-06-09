<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\ActivityStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UnexpectedValueException;

class ActivityStatusRequest extends BaseFormRequest
{
    private ActivityStatus|null $parsedStatus = null;


    protected function prepareForValidation(): void
    {
        if ($this->route('activityId')) {
            $this->merge([
                'data' => Arr::add($this->input('data', []), 'activityId', $this->route('activityId')),
            ]);
        }
    }

    public function rules(): array
    {
        $allStatusValues = collect(ActivityStatus::cases())->flatMap(static function ($status) {
            return [
                strtolower($status->name) => true,
                (string)$status->value => true,
            ];
        });


        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The fixed date and time for the activity in ISO 8601 format (Y-m-d\TH:i:s).
             * Example: "2025-04-30T14:30:00"
             * @var string
             * @example "2025-04-30T14:30:00"
             */
            'data.dateTimeFixed' => 'date_format:Y-m-d\TH:i:s',

            /**
             * The status of the activity.
             * Must be one of:
             * "ignore", "unallocated", "allocated", "committed", "sent", "downloaded",
             * "accepted", "travelling", "waiting", "onsite", "pendingcompletion",
             * "visitcomplete", "completed", "incomplete".
             * @var string
             * @example "allocated"
             */
            'data.status' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($allStatusValues) {
                    if (!$allStatusValues->has((string)$value) && !$allStatusValues->has(Str::lower($value))) {
                        $fail('The selected status is invalid.');
                    }
                },
            ],


            /**
             * The ID of the resource assigned to the activity.
             * Required if the activity status is greater than or equal to "allocated".
             * @var string|null
             * @example "resource-123"
             */
            'data.resourceId' => ['nullable', 'string'],
            'data.duration' => ['nullable', 'integer', 'gt:0'],
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);
        $statusesRequiringResource = collect(array_keys(ActivityStatus::statusesGreaterThanAllocated()))
            ->map(static fn($status) => Str::lower($status));

        $validator->sometimes('data.resourceId', ['required', 'string'], function ($input) use ($statusesRequiringResource) {
            $status = Str::lower(data_get($input, 'data.status', ''));

            return $statusesRequiringResource->contains($status);
        });
    }

    public function messages(): array
    {
        $allocatedLabel = ActivityStatus::ALLOCATED->label();

        return [
            'data.resourceId.required' => "The resource ID field is required when status is set to a value greater than or equal to {$allocatedLabel}.",
        ];
    }

    /**
     * After validation passes, convert the string status into the ActivityStatus enum.
     */
    protected function passedValidation(): void
    {
        $statusString = data_get($this->validated(), 'data.status', '');
        $statusString = strtolower($statusString);

        foreach (ActivityStatus::cases() as $case) {
            if (
                $case->value === $statusString ||
                strtolower($case->name) === $statusString
            ) {
                $this->parsedStatus = $case;
                return;
            }
        }


        throw new UnexpectedValueException("Unexpected status value '{$statusString}'");
    }

    /**
     * Get the parsed ActivityStatus enum after validation.
     */
    public function activityStatus(): ActivityStatus|null
    {
        return $this->parsedStatus;
    }
}
