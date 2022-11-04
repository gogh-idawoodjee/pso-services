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


        $activity = new PSOActivity($request, true);

        // should this go into AppointmentRequestPayloadPart or stay here?
        $appointment_duration = 'P' . ($request->appointment_template_duration ?: config('pso-services.defaults.activity.appointment_template_duration')) . 'D';
        // build the full activity object
        $activity_payload = $activity->FullActivityObject();
        $appointment_request_part_payload = $this->AppointmentRequestPayloadPart(
            $activity->getActivityID(),
            $request->appointment_template_id,
            $appointment_duration,
            $request->appointment_template_datetime

        );

        $input_ref = (new InputReference($request->description ?: 'Appointment Request', 'CHANGE', $request->dataset_id, $request->input_datetime))->toJson();
        $payload = $this->AppointmentRequestPayload($input_ref, $appointment_request_part_payload, $activity_payload);
        if ($request->send_to_pso) {
            $response = $this->IFSPSOAssistService->sendPayloadToPSO($payload, $this->token, $request->base_url, true);

            // process the response

            // collect the response from PSO

            // format it
            $appointment_request_id = collect($response->collect()->first()['Appointment_Offer'])->first()['appointment_request_id'];

            $valid_offers = collect($response->collect()->first()['Appointment_Offer'])->filter(function ($offer) {
                return collect($offer)->get('offer_value') > 0;
            })->map(function ($offer) {
                return collect($offer)->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value', 'prospective_resource_id');
            })->values();

            $invalid_offers = collect($response->collect()->first()['Appointment_Offer'])->filter(function ($offer) {
                return collect($offer)->get('offer_value') == 0;
            })->map(function ($offer) {
                return collect($offer)->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value');
            })->values();

            $best_offer_value = collect($response->collect()->first()['Appointment_Offer'])->max('offer_value');
            $best_offer = collect($response->collect()->first()['Appointment_Offer'])->where('offer_value', '=', $best_offer_value)
                ->map(function ($offer) {
                    return collect($offer)->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value', 'prospective_resource_id');
                })->first();//->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value', 'prospective_resource_id');


            // send it to API response
            $additional_data = [
                'description' => 'appointment_offers',
                'data' => [
                    'appointment_request_id' => $appointment_request_id,
                    'summary' => $valid_offers->count() . ' valid offers out of ' . collect($response->collect()->first()['Appointment_Offer'])->count() . ' returned.',
                    'best_offer' => $best_offer,
                    'valid_offers' => $valid_offers,
                    'invalid_offers' => $invalid_offers
                ]
            ];

            return $this->IFSPSOAssistService->apiResponse(200, "Payload sent to PSO. Offers Received", $payload, 'appointment_request', $additional_data);
        }
        return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO.", $payload, 'appointment_request');
    }

    private function AppointmentRequestPayloadPart($activity_id, $appointment_template_id, $appointment_template_duration, $appointment_template_datetime = null)
    {

        return [
            'activity_id' => $activity_id,
            'appointment_template_datetime' => $appointment_template_datetime ?: Carbon::now()->toAtomString(),
            'appointment_template_duration' => $appointment_template_duration,
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
