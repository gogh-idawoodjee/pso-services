<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\ProcessType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;

class LoadPsoRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {

        $commonRules = $this->commonRules();

        // override datasetId because it's required for load
        $commonRules['environment.datasetId'] = ['required','string'];
        $additionalRules =
            [
                'data.rotaId' => 'string', // if not included, assume same as dataset ID
                'data.dseDuration' => 'integer|required',
                'data.appointmentWindow' => 'integer',
                'data.processType' => [
                    'required',
                    new Enum(ProcessType::class),
                ],
                'data.description' => 'string',
                'data.datetime' => 'date',
                'data.includeBroadcast' => 'boolean',
                'data.keepPsoData' => 'boolean',
                'data.broadcastType' => 'integer|required_if:include_broadcast,true',
                'data.broadcastTrl' => 'url|required_if:include_broadcast,true',
                'data.id' => 'string'

            ];

        return array_merge($commonRules, $additionalRules);
    }
}
