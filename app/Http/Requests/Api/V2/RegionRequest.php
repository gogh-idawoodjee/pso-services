<?php

namespace App\Http\Requests\Api\V2;

class RegionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * Region (RAM_Division) ids to create.
             *
             * @var string[]
             *
             * @example ["NORTH", "SOUTH"]
             */
            'data.regions' => ['required', 'array', 'min:1'],
            'data.regions.*' => ['string'],

            /**
             * Descriptions for each region, in the same order as data.regions.
             * Ignored (all regions get an auto-generated description) unless
             * the count matches data.regions exactly.
             *
             * @var string[]|null
             *
             * @example ["Northern territory", "Southern territory"]
             */
            'data.descriptions' => ['nullable', 'array'],
            'data.descriptions.*' => ['string'],

            /**
             * The parent division id for all regions in this request.
             *
             * @var string|null
             *
             * @example "CANADA"
             */
            'data.regionParent' => ['nullable', 'string'],

            /**
             * The division type id for all regions in this request.
             *
             * @var string|null
             *
             * @example "PROVINCE"
             */
            'data.regionCategory' => ['nullable', 'string'],

            /**
             * Whether to send the division through to the scheduling engine (as a region). Defaults to true.
             *
             * @var bool|null
             *
             * @example true
             */
            'data.send' => ['nullable', 'boolean'],
        ];

        return array_merge($commonRules, $additionalRules);
    }
}
