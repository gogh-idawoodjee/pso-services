<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Validation\Validator;

class ResourceStoreRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The PSO resource type ID shared by every resource created in this request.
             *
             * @var string
             *
             * @example "FIELD_TECH"
             */
            'data.resourceTypeId' => ['required', 'string'],

            /**
             * How many resources to create. Defaults to the number of lat entries given.
             *
             * @var int|null
             *
             * @example 3
             */
            'data.resourcesToCreate' => ['nullable', 'integer', 'min:1', 'max:50'],

            /**
             * Latitude for each resource's starting location, in the same order as long.
             *
             * @var float[]
             *
             * @example [43.65107, 43.70011]
             */
            'data.lat' => ['required', 'array', 'min:1'],
            'data.lat.*' => ['numeric', 'between:-90,90'],

            /**
             * Longitude for each resource's starting location, in the same order as lat.
             *
             * @var float[]
             *
             * @example [-79.347015, -79.4163]
             */
            'data.long' => ['required', 'array', 'min:1'],
            'data.long.*' => ['numeric', 'between:-180,180'],

            /**
             * Full names ("First Last") for each resource being created, in order.
             * Resources beyond the given names get a randomly generated name.
             *
             * @var string[]|null
             *
             * @example ["John Smith", "Jane Doe"]
             */
            'data.names' => ['nullable', 'array'],
            'data.names.*' => ['string'],

            /**
             * Explicit resource ids, in order. Resources beyond the given ids get
             * an id derived from their (given or generated) name.
             *
             * @var string[]|null
             *
             * @example ["RES-001", "RES-002"]
             */
            'data.ids' => ['nullable', 'array'],
            'data.ids.*' => ['string'],

            /**
             * Skill ids applied to every resource created in this request.
             *
             * @var string[]|null
             *
             * @example ["ELECTRICAL", "PLUMBING"]
             */
            'data.skills' => ['nullable', 'array'],
            'data.skills.*' => ['string'],

            /**
             * Region (division) ids every resource created in this request belongs to.
             *
             * @var string[]|null
             *
             * @example ["NORTH"]
             */
            'data.regions' => ['nullable', 'array'],
            'data.regions.*' => ['string'],
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator) {
            $lat = $this->input('data.lat', []);
            $long = $this->input('data.long', []);

            if (is_array($lat) && is_array($long) && count($lat) !== count($long)) {
                $validator->errors()->add('data.long', 'data.long must have the same number of entries as data.lat.');
            }
        });
    }
}
