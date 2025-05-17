<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Contracts\Validation\ValidationRule;

class ScheduleExceptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The ID of the exception type.
             * @var int
             * @example 1
             */
            'data.exceptionTypeId' => 'required|integer',

            /**
             * A human-readable label for the exception.
             * @var string
             * @example "Sick Leave"
             */
            'data.label' => 'required|string',

            /**
             * The value of the exception (e.g., a date or duration).
             * @var string
             * @example "2025-05-17"
             */
            'data.value' => 'required|string',

            /**
             * The ID of the activity affected by this exception.
             * Required if resourceId is not provided.
             * @var string|null
             * @example "ACT123"
             */
            'data.activityId' => 'required_without:data.resourceId|nullable|string',

            /**
             * The ID of the resource affected by this exception.
             * Required if activityId is not provided.
             * @var string|null
             * @example "RES456"
             */
            'data.resourceId' => 'required_without:data.activityId|nullable|string',
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->after(function ($validator) {
            $activityId = $this->input('data.activityId');
            $resourceId = $this->input('data.resourceId');

            if (filled($activityId) && filled($resourceId)) {
                $validator->errors()->add('data.activityId', 'Only one of activityId or resourceId should be provided, not both.');
                $validator->errors()->add('data.resourceId', 'Only one of activityId or resourceId should be provided, not both.');
            }
        });
    }
}
