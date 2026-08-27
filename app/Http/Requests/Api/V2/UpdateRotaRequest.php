<?php

namespace App\Http\Requests\Api\V2;

use App\Traits\V2\ValidatesBroadcasts;
use Illuminate\Validation\Validator;

class UpdateRotaRequest extends BaseFormRequest
{
    use ValidatesBroadcasts;

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        // override datasetId because it's required for rota
        $commonRules['environment.datasetId'] = ['required', 'string'];

        $additionalRules = [
            'data.rotaId' => 'string', // if not included, assume same as dataset ID
            'data.description' => 'string',
            'data.datetime' => 'date',
            'data.id' => 'string',
        ];

        return array_merge($commonRules, $additionalRules, $this->broadcastRules());
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $this->requireBroadcastParameters($validator);
    }
}
