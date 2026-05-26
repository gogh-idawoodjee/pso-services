<?php

namespace App\Http\Requests\Api\V2;

class HealthCheckRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return $this->commonRules();
    }
}
