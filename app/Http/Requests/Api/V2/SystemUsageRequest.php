<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use Override;

class SystemUsageRequest extends BaseGetFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $rules = [
            'minDate' => ['nullable', 'date'],
            'maxDate' => ['nullable', 'date'],
        ];

        return array_merge($commonRules, $rules);
    }

    #[Override] public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);
        $validator->after(function ($validator) {
            $min = $this->input('minDate');
            $max = $this->input('maxDate');

            // If one is present but the other is missing
            if (($min && !$max) || (!$min && $max)) {
                $validator->errors()->add('minDate', 'Both minDate and maxDate must be provided together.');
                $validator->errors()->add('maxDate', 'Both minDate and maxDate must be provided together.');
            }

            // If both are present and minDate is after maxDate
            if ($min && $max && strtotime($min) > strtotime($max)) {
                $validator->errors()->add('minDate', 'minDate must be before or equal to maxDate.');
            }
        });
    }
}
