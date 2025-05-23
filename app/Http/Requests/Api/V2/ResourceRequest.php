<?php

namespace App\Http\Requests\Api\V2;


class ResourceRequest extends BaseGetFormRequest
{

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $rules = [];
        return array_merge($commonRules, $rules);
    }

}
