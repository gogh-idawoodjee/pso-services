<?php

namespace App\Http\Requests\Api\V2;

class LoadTestRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $commonRules['environment.datasetId'] = ['required', 'string'];

        $additionalRules = [
            /**
             * Number of synthetic appointment requests to run.
             * @var int
             * @example 10
             */
            'data.taskCount' => ['required', 'integer', 'min:1'],

            /**
             * Prefix used to build synthetic activity IDs.
             * @var string
             * @example "loadtest-"
             */
            'data.taskPrefix' => ['required', 'string'],

            /**
             * Days offset from now to start the synthetic appointment window.
             * @var int
             * @example 0
             */
            'data.relativeStart' => ['required', 'integer'],

            /**
             * Whether to run a check-appointed step before accepting each offer.
             * @var boolean
             * @example false
             */
            'data.checkAppointed' => ['boolean'],

            /**
             * Latitude values to randomly draw from for synthetic activities.
             * @var array
             * @example ["43.647", "43.669"]
             */
            'data.dataLat' => ['array'],
            'data.dataLat.*' => ['string'],

            /**
             * Longitude values to randomly draw from for synthetic activities.
             * @var array
             * @example ["-79.377", "-79.388"]
             */
            'data.dataLong' => ['array'],
            'data.dataLong.*' => ['string'],
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
