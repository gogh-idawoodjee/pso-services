<?php

namespace App\Http\Requests\Api\V2;

class ActivityDeleteRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * List of activity IDs to delete.
             * @var string[]
             * @example ["act-123", "act-456", "act-789"]
             */
            'data.activities' => 'array|required',

            /**
             * Each activity ID must be a non-null string.
             * @var string
             * @example "act-123"
             */
            'data.activities.*' => 'required|string',

            /**
             * Reference datetime for this write, used as "now" by PSO instead of
             * the actual current time when supplied. Defaults to now when omitted.
             *
             * @var string|null
             *
             * @example "2025-04-30T14:30:00"
             */
            'data.inputDatetime' => ['nullable', 'date'],
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
