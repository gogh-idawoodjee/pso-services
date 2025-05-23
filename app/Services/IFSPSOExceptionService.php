<?php

namespace App\Services;

use App\Classes\V1\InputReference;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IFSPSOExceptionService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function ExceptionPayload(Request $request)
    {
        $exception_id = Str::orderedUuid()->getHex()->toString();

        $custom_exception_payload = $this->CustomExceptionPayload($exception_id, $request->activity_id, $request->resource_id, $request->schedule_exception_type_id);
        $custom_exception_data_payload = $this->CustomExceptionDataPayload($exception_id, $request->label, $request->value);

        $input_ref = (
        new InputReference(
            'adding custom exception',
            'Change',
            $request->dataset_id)
        )->toJson();

        $fullpayload = [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Custom_Exception' => $custom_exception_payload,
                'Custom_Exception_Data' => $custom_exception_data_payload
            ]
        ];


//        return $fullpayload;

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $fullpayload, $this->token, $request->base_url, 'Create New Exception Via ' . $this->service_name);
    }

    private function CustomExceptionPayload($id, $activity_id, $resource_id, $schedule_exception_type_id)
    {

        $exception = compact('id', 'schedule_exception_type_id');

        if ($activity_id) {
            $exception = Arr::add($exception, 'activity_id', $activity_id);
        }

        if ($resource_id) {
            $exception = Arr::add($exception, 'resource_id', $resource_id);
        }

        return $exception;
    }

    private function CustomExceptionDataPayload($id, $label, $value)
    {
        return [

            'custom_exception_id' => $id,
            'label' => $label,
            'sequence' => 1,
            'value' => $value

        ];
    }


}
