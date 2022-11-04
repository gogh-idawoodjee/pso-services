<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSOActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class IFSPSOAppointmentService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function getAppointment(Request $request)//: JsonResponse
    {

        $payload = null;
        $activity = new PSOActivity($request, true);

        // build the full activity object
        $activity_payload = $activity->FullActivityObject();
        $appointment_request_part_payload = $this->AppointmentRequestPayloadPart(
            $activity->getActivityID(),
            'EST',
            $request->appointment_template_datetime,$request->appointment_template_duration
        );

        $input_ref = (new InputReference($request->description ?: 'Appointment Request', 'CHANGE', $request->dataset_id, $request->input_datetime))->toJson();

        return $this->AppointmentRequestPayload($input_ref, $appointment_request_part_payload, $activity_payload);


        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso, $payload, $this->token, $request->base_url, 'Event Set and Rota Updated', false, $request->dataset_id,
        );
    }

    private function AppointmentRequestPayloadPart($activity_id, $appointment_template_id, $appointment_template_datetime = null, $appointment_template_duration = null)
    {
        return [
            'activity_id' => $activity_id,
            'appointment_template_datetime' => $appointment_template_datetime ?: Carbon::now()->toAtomString(),
            'appointment_template_duration' => $appointment_template_duration ?: config('pso-services.defaults.activity.appointment_template_duration'),
            'appointment_template_id' => $appointment_template_id,
            'id' => Str::orderedUuid()->getHex()->toString(),
            'offer_expiry_datetime' => Carbon::now()->addMinutes(5)->toAtomString()
        ];
    }

    public function AppointmentRequestPayload($input_reference, $appointment_request, $activity_payload): array
    {

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Appointment_Request' => $appointment_request,
                'Activity' => $activity_payload['Activity'],
                'Activity_Skill' => $activity_payload['Activity_Skill'],
                'Activity_SLA' => $activity_payload['Activity_SLA'],
                'Activity_Status' => $activity_payload['Activity_Status'],
                'Location' => $activity_payload['Location'],
            ]
        ];
    }


}
