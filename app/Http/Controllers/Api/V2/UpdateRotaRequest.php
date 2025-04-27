<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\BaseFormRequest;

class UpdateRotaRequest extends BaseFormRequest
{
    public function rules(): array
    {

        $commonRules = $this->commonRules();

        // override datasetId because it's required for rota
        $commonRules['environment.datasetId'] = ['required', 'string'];
        $additionalRules =
            [
                'data.rotaId' => 'string', // if not included, assume same as dataset ID
                'data.description' => 'string',
                'data.datetime' => 'date',
                'data.includeBroadcast' => 'boolean',
                'data.broadcastType' => 'integer|required_if:include_broadcast,true',
                'data.broadcastTrl' => 'url|required_if:include_broadcast,true',
                'data.id' => 'string'
            ];

        return array_merge($commonRules, $additionalRules);
    }

}
