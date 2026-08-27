<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\ProcessType;
use App\Traits\V2\ValidatesBroadcasts;
use Illuminate\Validation\Rules\Enum;

class LoadPsoRequest extends BaseFormRequest
{
    use ValidatesBroadcasts;

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        /**
         * The dataset ID to use in PSO.
         *
         * @var string
         *
         * @example "dataset_123"
         */
        $commonRules['environment.datasetId'] = ['required', 'string']; // note that datasetID is always required for Load

        $additionalRules = [
            /**
             * The rota ID associated with the load.
             * Defaults to dataset ID if not provided.
             *
             * @var string
             *
             * @example "rota-001"
             */
            'data.rotaId' => ['string'],

            /**
             * Duration of the Dynamic Scheduling Engine run, in minutes.
             *
             * @var int
             *
             * @example 120
             */
            'data.dseDuration' => 'integer|required',

            /**
             * Appointment window duration, in minutes.
             *
             * @var int
             *
             * @example 30
             */
            'data.appointmentWindow' => 'integer',

            /**
             * The type of processing to perform.
             * Must be one of: "DYNAMIC", "APPOINTMENT", "REACTIVE", "STATIC".
             *
             * @var string
             *
             * @example "DYNAMIC"
             */
            'data.processType' => [
                'required',
                new Enum(ProcessType::class),
            ],

            /**
             * Description of the PSO load.
             *
             * @var string
             *
             * @example "PSO load for daily operations"
             */
            'data.description' => 'string',

            /**
             * Datetime associated with the load.
             *
             * @var string
             *
             * @example "2025-04-30T14:30:00"
             */
            'data.datetime' => 'date',

            /**
             * Whether to keep existing PSO data during the load.
             *
             * @var bool
             *
             * @example false
             */
            'data.keepPsoData' => 'boolean',

            /**
             * The ID associated with the PSO load.
             *
             * @var string
             *
             * @example "load-123"
             */
            'data.id' => 'string',

            /**
             * Whether to include ARP data in the PSO load.
             * If true, rotaId is required. Source Data and Source Data Params will be included.
             *
             * @var bool
             *
             * @example true
             */
            'data.includeArpData' => 'boolean',
        ];

        return array_merge($commonRules, $additionalRules, $this->broadcastRules());
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->sometimes('data.rotaId', 'required|string', static function ($input) {
            return (bool) data_get($input, 'data.includeArpData') === true;
        });

        $this->requireBroadcastParameters($validator);
    }
}
