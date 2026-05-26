<?php

namespace App\Http\Requests\Api\V2;

class ResourceRequest extends BaseGetFormRequest
{
    public function rules(): array
    {
        return $this->commonRules();
    }
}
