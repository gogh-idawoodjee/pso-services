<?php

namespace App\Http\Requests\Api\V2;

class ResourceRequest extends BaseGetFormRequest
{
    public function rules(): array
    {
        return $this->commonRules();
    }

    /**
     * This is a GET request — all parameters (datasetId, baseUrl, accountId,
     * etc.) are read from headers via commonRules(), not the request body.
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
