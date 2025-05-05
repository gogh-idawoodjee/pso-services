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
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
