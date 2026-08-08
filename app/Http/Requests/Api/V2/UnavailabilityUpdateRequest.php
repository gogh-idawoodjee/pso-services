<?php

namespace App\Http\Requests\Api\V2;

class UnavailabilityUpdateRequest extends UnavailabilityRequest
{
    protected function prepareForValidation(): void
    {
        $routeId = $this->route('unavailabilityId');

        if ($routeId) {
            $ids = array_values(array_unique([
                ...(array) $this->input('data.unavailabilityIds', []),
                $routeId,
            ]));

            $this->merge([
                'data' => array_merge((array) $this->input('data', []), [
                    'unavailabilityIds' => $ids,
                ]),
            ]);
        }
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            /**
             * The IDs of the existing unavailabilities to update. Always includes
             * the ID from the route; pass additional IDs to update several
             * unavailabilities that share the same new time/resource/category in
             * one call.
             * @var string[]
             * @example ["UNAVAIL-001", "UNAVAIL-002"]
             */
            'data.unavailabilityIds' => ['required', 'array', 'min:1'],
            'data.unavailabilityIds.*' => ['string'],

            /**
             * Only ARP (Automated Resource Planning) unavailabilities can be
             * updated in place today. Non-ARP unavailabilities are represented
             * as private schedule activities and must be deleted and recreated.
             * @var bool
             * @example true
             */
            'data.isArpObject' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== true) {
                        $fail('Only ARP unavailabilities can be updated in place. Delete and recreate non-ARP unavailabilities instead.');
                    }
                },
            ],
        ]);
    }
}
