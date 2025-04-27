<?php

namespace App\Http\Requests\Api\V2;


class AppointmentRequest extends BaseFormRequest
{
    public function rules(): array
    {


        $commonRules = $this->commonRules();

        $additionalRules = [
            'data.activityId' => 'string|required',
            'data.slotUsageRuleId' => 'string',
            'data.skills' => 'array',
            'data.regions' => 'array',
            'data.activityTypeId' => 'string|required',
            'data.duration' => 'integer|lt:1440|required',
            'data.baseValue' => 'integer|gt:0',
            'data.visitId' => 'integer|gt:0',
            'data.priority' => 'integer',
            'data.slaStart' => 'date_format:Y-m-d\TH:i:s|before:sla_end|required',
            'data.slaEnd' => 'date_format:Y-m-d\TH:i:s|after:sla_start|required',
            'data.slaTypeId' => 'string|required',
            'data.appointmentTemplateId' => 'string|required',
            'data.appointmentTemplateDuration' => 'integer|gte:0',
            'data.appointmentTemplateDatetime' => 'date_format:Y-m-d\TH:i:s',
            'data.appointmentBaseDateTime' => 'date_format:Y-m-d\TH:i:s',
            'data.inputDatetime' => 'date_format:Y-m-d\TH:i:s',
            'data.lat' => 'numeric|between:-90,90|required',
            'data.long' => 'numeric|between:-180,180|required',
            'data.timezone' => 'timezone:all',
            'data.splitAllowed' => 'boolean'
        ];

        return array_merge($commonRules, $additionalRules);
    }


}
