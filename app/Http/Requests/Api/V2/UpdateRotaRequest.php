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
            /**
             * The rota ID to update. Defaults to the dataset ID if not provided.
             *
             * @var string
             *
             * @example "rota-001"
             */
            'data.rotaId' => 'string',

            /**
             * Description stored on the Input_Reference for this rota update.
             *
             * @var string
             *
             * @example "Update Rota"
             */
            'data.description' => 'string',

            /**
             * Reference datetime for this write, used as "now" by PSO instead of
             * the actual current time when supplied. Defaults to now when omitted.
             *
             * @var string
             *
             * @example "2025-04-30T14:30:00"
             */
            'data.inputDatetime' => 'date',

            /**
             * Unique ID for the Input_Reference. Generated if omitted.
             *
             * @var string
             *
             * @example "a2df0c9da1a440a7b7b452d88eb8fcd3"
             */
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
