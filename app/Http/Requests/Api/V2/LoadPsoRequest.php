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

        /**
         * The dataset ID to use in PSO.
         * @var string
         * @example "dataset_123"
         */
        $commonRules['environment.datasetId'] = ['required', 'string']; // note that datasetID is always required for Load

        $additionalRules = [
            /**
             * The rota ID associated with the load.
             * Defaults to dataset ID if not provided.
             * @var string
             * @example "rota-001"
             */
            'data.rotaId' => ['string'],

            /**
             * Duration of the Dynamic Scheduling Engine run, in minutes.
             * @var int
             * @example 120
             */
            'data.dseDuration' => 'integer|required',

            /**
             * Appointment window duration, in minutes.
             * @var int
             * @example 30
             */
            'data.appointmentWindow' => 'integer',

            /**
             * The type of processing to perform.
             * Must be one of: "DYNAMIC", "APPOINTMENT", "REACTIVE", "STATIC".
             * @var string
             * @example "DYNAMIC"
             */
            'data.processType' => [
                'required',
                new Enum(ProcessType::class),
            ],

            /**
             * Description of the PSO load.
             * @var string
             * @example "PSO load for daily operations"
             */
            'data.description' => 'string',

            /**
             * Datetime associated with the load.
             * @var string
             * @example "2025-04-30T14:30:00"
             */
            'data.datetime' => 'date',

            /**
             * Whether to include broadcast in the PSO load.
             * @var boolean
             * @example true
             */
            'data.includeBroadcast' => 'boolean',

            /**
             * Whether to keep existing PSO data during the load.
             * @var boolean
             * @example false
             */
            'data.keepPsoData' => 'boolean',

            /**
             * Broadcast type.
             * Required if includeBroadcast is true.
             * @var int
             * @example 1
             */
            'data.broadcastType' => 'integer|required_if:include_broadcast,true',

            /**
             * Broadcast URL.
             * Required if includeBroadcast is true.
             * @var string
             * @example "https://example.com/broadcast.trl"
             */
            'data.broadcastUrl' => 'url|required_if:include_broadcast,true',

            /**
             * The ID associated with the PSO load.
             * @var string
             * @example "load-123"
             */
            'data.id' => 'string',

            /**
             * Whether to include ARP data in the PSO load.
             * If true, rotaId is required. Source Data and Source Data Params will be included.
             * @var boolean
             * @example true
             */
            'data.includeArpData' => 'boolean',
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator($validator): void
    {
        parent::withValidator($validator);

        $validator->sometimes('data.rotaId', 'required|string', static function ($input) {
            return (bool)data_get($input, 'data.includeArpData') === true;
        });
    }
}
