<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSOActivity;
use App\Classes\PSOActivitySLA;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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

            $appointment_request_id = collect($response->collect()->first()['Appointment_Offer'])->first()['appointment_request_id'];

            // format it

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

    public function checkAppointed($request, $appointment_request_id): JsonResponse
    {

        $input_ref = (new InputReference($request->description ?: 'Appointment Slot Check', 'CHANGE', $request->dataset_id))->toJson();
        $offer_response_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, $request->appointment_offer_id);
        $payload = $this->AppointmentOfferResponsePayload($input_ref, $offer_response_payload);

        if ($request->send_to_pso) {
            $response = $this->IFSPSOAssistService->sendPayloadToPSO($payload, $this->token, $request->base_url, true);

            // todo may need to validate if this returns correctly or fails
            $summary = collect($response->collect()->first()['Appointment_Summary']);

            $additional_data = [
                'description' => 'appointment_summary',
                'data' => [
                    'appointment_request_id' => $summary->appointment_request_id,
                    'slot_is_available' => $summary->appointed,
                    'full_summary' => $summary->toJson()
                ]
            ];

            return $this->IFSPSOAssistService->apiResponse(200, "Payload sent to PSO. Slot Validated", $payload, 'appointment_summary_slot_check', $additional_data);

        }

        return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO.", $payload, 'appointed_check');

    }

    public function declineAppointment(Request $request, $appointment_request_id): JsonResponse
    {
        // lookup the activity
        $activity = [];
        $activity_to_trash = new IFSPSOActivityService($request->base_url, $this->token, null, null, $request->account_id, true);
        $activity_request = new Request(
            [
                'activity_id' => $request->activity_id,
                'sla_type_id' => $activity->sla_type_id,
                'priority' => $activity->prioirty,
                'start_based' => $activity->start_based,
                'dataset_id' => $request->dataset_id,
                'send_tp_pso' => true
            ]);


        // trash the activity
        $activity_to_trash->deleteActivity($activity_request, "deleting temp activity - declined appointments");

        // trash the SLA
        $activity_to_trash->deleteSLA($activity_request, "deleting activity sla - declined appointments");

        // decline the appointment
        $decline_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, -1, true);
        $input_ref = (new InputReference($request->description ?: 'Declining Appointments', 'CHANGE', $request->dataset_id))->toJson();
        $payload = $this->AppointmentOfferResponsePayload($input_ref, $decline_payload);
        return $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, 'appointment_offers_declined');
    }


    public function acceptAppointment(Request $request, $appointment_request_id)//: JsonResponse
    {

        $activity = new IFSPSOActivityService($request->base_url, $this->token, null, null, $request->account_id, true);
        // parameters needed to do the GET request on activity
        $activity_request_get = new Request(
            [
                'activity_id' => $request->activity_id,
                'dataset_id' => $request->dataset_id,
                'base_url' => $request->base_url
            ]);
        $activity_data = $activity->getActivity($activity_request_get, $request->activity_id);
//        return $activity_data;

        if ($activity->activityExists()) {
            // parameters needed to trash the activity SLA
            $activity_data_get = [
                'sla_type_id' => collect($activity_data->first())['Activity_SLA']['sla_type_id'],
                'priority' => collect($activity_data->first())['Activity_SLA']['priority'],
                'start_based' => collect($activity_data->first())['Activity_SLA']['start_based'],

            ];
//            return $activity_data_get['priority'];
        } else {
            return $this->IFSPSOAssistService->apiResponse(404, 'No Such Activity', $request->activity_id, 'activity_id');
        }

        $activity_to_trash = new IFSPSOActivityService($request->base_url, $this->token, null, null, $request->account_id, true);
        $activity_request_trash = new Request(
            [
                'activity_id' => $request->activity_id,
                'sla_type_id' => $activity_data_get['sla_type_id'],
                'priority' => $activity_data_get['priority'],
                'start_based' => $activity_data_get['start_based'],
                'dataset_id' => $request->dataset_id,
                'base_url' => $request->base_url
            ]);
        // trash the old SLA
        $activity_to_trash->deleteSLA($activity_request_trash, "deleting activity sla - declined appointments");

        // generate the new SLA
        $new_sla = new PSOActivitySLA($request->sla_type_id, $request->sla_start, $request->sla_end, $request->sla_priority, $request->sla_start_based);
        // send the SLA
        $input_ref = (new InputReference($request->description ?: 'Updated Activity SLA', 'CHANGE', $request->dataset_id))->toJson();
        $payload = [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Activity_SLA' => $new_sla->toJson($request->activity_id)
            ]];
        $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, 'updated_sla');

        // accept the slot
        $accept_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, $request->appointment_offer_id, true);
        $input_ref = (new InputReference($request->description ?: 'Accepted Appointment Slot', 'CHANGE', $request->dataset_id))->toJson();
        $payload = $this->AppointmentOfferResponsePayload($input_ref, $accept_payload);
        return $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, 'appointment_offer_accepted');

        // todo send the updated activity

    }

    private function AppointmentOfferResponsePayload($input_reference, $offer_response)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Appointment_Offer_Response' => $offer_response
            ]
        ];
    }

    private function AppointmentOfferResponsePayloadPart($appointment_request_id, $appointment_offer_id, $input_updated = false)
    {
        return [
            'appointment_request_id' => $appointment_request_id,
            'appointment_offer_id' => $appointment_offer_id,
            'input_updated' => $input_updated
        ];
    }

    private function AppointmentRequestPayload($input_reference, $appointment_request, $activity_payload): array
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
