<?php

namespace App\Http\Requests\Api\V2;

class UnavailabilityUpdateRequest extends UnavailabilityRequest
{

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'data.unavailability_id' => 'required|array',
        ]);
    }

}
