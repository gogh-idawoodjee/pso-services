<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Models\V2\PSOAppointment;
use App\Services\IFSPSOAppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JsonException;

class PSOAppointmentController extends Controller
{

    // return appointment by request_id

    public function index(PSOAppointment $appointment_request_id)
    {

        $appointment_details = $appointment_request_id->toJson();

        return $appointment_request_id;
    }

    /**
     * see if the appointment is still available
     * still using HTTP POST instead of HTTP GET for show method
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, $appointment_request_id)
    {
        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'appointment_offer_id' => 'integer|gt:-1|required'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $appointed = new IFSPSOAppointmentService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$appointed->isAuthenticated()) {
            return PSOHelper::notAuth();

        }

        return $appointed->checkAppointed($request, $appointment_request_id);
    }


    /**
     * request the appointment
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|JsonException
     */
    public function store(Request $request)
    {

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'activity_id' => 'string|required',
            'activity_type_id' => 'string|required',
            'duration' => 'integer|lt:1440|required',
            'base_value' => 'integer|gt:0',
            'visit_id' => 'integer|gt:0',
            'priority' => 'integer',
            'sla_start' => 'date_format:Y-m-d\TH:i:s|before:sla_end|required',
            'sla_end' => 'date_format:Y-m-d\TH:i:s|after:sla_start|required',
            'sla_type_id' => 'string|required',
            'appointment_template_id' => 'string|required',
            'appointment_template_duration' => 'integer|gte:0',
//            'appointment_template_datetime' => 'date|after:input_datetime',
            'appointment_template_datetime' => 'date_format:Y-m-d\TH:i:s',
            'input_datetime' => 'date_format:Y-m-d\TH:i:s',
//            'input_datetime' => 'date|before:appointment_template_datetime',
            'lat' => 'numeric|between:-90,90|required',
            'long' => 'numeric|between:-180,180|required',
            'timezone' => 'timezone:all'

        ]);

        PSOHelper::ValidateSendToPSO($request);


        $appointment = new IFSPSOAppointmentService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$appointment->isAuthenticated()) {
            return PSOHelper::notAuth();

        }

        return $appointment->getAppointment($request);


    }

    /**
     * accept the appointment slot.
     *
     * @param Request $request
     * @param $appointment_request_id
     * @return JsonResponse
     * @throws ValidationException|JsonException
     */
    public function update(Request $request, $appointment_request_id)
    {
        $request->validate([
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],

            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'sla_priority' => 'integer',
            'sla_start_based' => 'boolean',
            'sla_type_id' => 'string|required',
            'appointment_offer_id' => 'integer|gt:-1|required'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $appointment = new IFSPSOAppointmentService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true);

        if (!$appointment->isAuthenticated()) {
            return PSOHelper::notAuth();

        }

        return $appointment->acceptAppointment($request, $appointment_request_id);

    }

    /**
     * reject the appointment slots.
     *
     * @param Request $request
     * @param $appointment_request_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(Request $request, $appointment_request_id)
    {
        $request->validate([
            'base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $appointment = new IFSPSOAppointmentService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true);


        if (!$appointment->isAuthenticated()) {
            return PSOHelper::notAuth();
        }
        return $appointment->declineAppointment($request, $appointment_request_id);

    }

}
