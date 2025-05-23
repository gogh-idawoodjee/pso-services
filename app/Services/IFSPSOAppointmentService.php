<?php

namespace App\Services;

use App\Classes\V1\InputReference;
use App\Classes\V1\PSOActivity;
use App\Helpers\PSOHelper;
use App\Models\PSOAppointment;
use Carbon\Carbon;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JsonException;


class IFSPSOAppointmentService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    /**
     * @throws JsonException
     */
    public function getAppointment(Request $request)//: JsonResponse
    {

        $activity = new PSOActivity($request, true);

        $request->offsetUnset('password');
        $request->offsetSet('password', Hash::make($request->password));

        // build the full activity object
        $activity_payload = $activity->FullActivityObject();
        $appointment_request_part_payload = $this->AppointmentRequestPayloadPart(
            $request->input_datetime,
            $activity->getActivityID(),
            $request->appointment_template_id,
            $request->appointment_template_duration,
            $request->appointment_template_datetime,
            $request->slot_usage_rule_id ?: null

        );


        $input_ref = (new InputReference($request->description ?: 'Appointment Request', 'CHANGE', $request->dataset_id, $request->input_datetime))->toJson();
        $payload = $this->AppointmentRequestPayload($input_ref, $appointment_request_part_payload, $activity_payload);

        if ($request->send_to_pso) {


            $response = $this->IFSPSOAssistService->sendPayloadToPSO($payload, $this->token, $request->base_url, true);
            // collect the response from PSO
            if ($response->status() > 200 || !$response->collect()->first()) {
                return $this->IFSPSOAssistService->apiResponse(500, "Check PSO Events. Probably bad data.", $payload);
            }

            if (!Arr::has($response->collect()->first(), 'Appointment_Offer')) {
                return $this->IFSPSOAssistService->apiResponse('500', 'Double check the gantt for resources and appointment template ID', $payload);
            }


            // todo catch that this should always be an array that is returned instead of just an object
            $appointment_request_id = collect($response->collect()->first()['Appointment_Offer'])->first()['appointment_request_id'];


            // format it

            $best_offer_value = collect($response->collect()->first()['Appointment_Offer'])->max('offer_value');

            $best_offer = collect($response->collect()->first()['Appointment_Offer'])->where('offer_value', '=', $best_offer_value)
                ->map(function ($offer) use ($request) {
                    return $this->getPut($offer, null, $request->timezone);
                })->first();


            $valid_offers = collect($response->collect()->first()['Appointment_Offer'])->filter(function ($offer) {
                return collect($offer)->get('offer_value') !== "0";
            })->map(function ($offer) use ($best_offer, $request) {
                return $this->getPut($offer, $best_offer['id'], $request->timezone);
            })->values();

            // if no valid offers return 404?
            if (!count($valid_offers)) {

                $this->save_appointment_request(
                    $appointment_request_part_payload,
                    $request->all(), // right now we're storing this in a json dump, probably should be cleaned up
                    $payload,
                    $activity,
                    $request->dataset_id,
                    $input_ref['id'],
                    $response,
                    null,
                    null,
                    null,
                    $request->run_id
                );


                return $this->IFSPSOAssistService->apiResponse(404, "Sorry Bro, no slots returned", $payload, "original_request", ['description' => 'appointment_request_id', 'data' => $appointment_request_id]);
            }


            $invalid_offers = collect($response->collect()->first()['Appointment_Offer'])->filter(function ($offer) {
                return collect($offer)->get('offer_value') === "0";
            })->map(function ($offer) {
                return collect($offer)->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value');
            })->values();


            $offer_values = collect($response->collect()->first()['Appointment_Offer'])->map(function ($offer) {
                return collect($offer)->only('id', 'offer_value', 'window_start_datetime', 'prospective_resource_id');
            })->values();


            // send it to API response
            $additional_data = [
                'description' => 'appointment_offers',
                'data' => [
                    'appointment_request_id' => $appointment_request_id,
                    'summary' => $valid_offers->count() . ' valid offers out of ' . collect($response->collect()->first()['Appointment_Offer'])->count() . ' returned.',
                    'best_offer' => $best_offer->get('prospective_resource_id') ? $best_offer : 'no valid offers returned',

                    'valid_offers' => $valid_offers,
                    'invalid_offers' => $invalid_offers,
                    'offer_values' => $offer_values
                ]
            ];

            $this->save_appointment_request(
                $appointment_request_part_payload,
                $request->all(), // right now we're storing this in a json dump, probably should be cleaned up
                $payload,
                $activity,
                $request->dataset_id,
                $input_ref['id'],
                $response,
                $valid_offers,
                $invalid_offers,
                $best_offer,
                $request->run_id
            );

            return $this->IFSPSOAssistService->apiResponse(200, "Payload sent to PSO. Offers Received", ["appointment_request_input" => $payload, "pso_response" => $response->collect()], 'actual_transactions', $additional_data);
        }
        return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO.", $payload, 'appointment_request');
    }

    private function AppointmentRequestPayloadPart($input_datetime, $activity_id, $appointment_template_id, $appointment_template_duration, $appointment_template_datetime = null, $slot_usage_rule_id = null)
    {

        // todo this needs to be parameterized
        // moore importantly, it needs to be checked against the sla_end and match accordingly
        $appointment_duration = PSOHelper::setPSODurationDays($appointment_template_duration ?: config('pso-services.defaults.activity.appointment_template_duration'));

        $payload =
            [
                'activity_id' => $activity_id,
                'appointment_template_datetime' => $appointment_template_datetime ?: Carbon::now()->toAtomString(),
                'appointment_template_duration' => $appointment_duration,
                'appointment_template_id' => $appointment_template_id,
                'id' => Str::orderedUuid()->getHex()->toString(),
                'offer_expiry_datetime' => $input_datetime ? Carbon::parse($input_datetime)->addMinutes(5)->toAtomString() : Carbon::now()->addMinutes(5)->toAtomString()
            ];

        if ($slot_usage_rule_id) {
            $payload = Arr::add($payload, 'slot_usage_rule_id', $slot_usage_rule_id);
        }

        return $payload;
    }

    /**
     * @throws JsonException
     */
    public function checkAppointed($request, $appointment_request_id)
    {


        try {
            $appointment_request = PSOAppointment::where('id', $appointment_request_id)->firstOrFail();

//return $appointment_request;

        } catch (ModelNotFoundException) {
            return $this->IFSPSOAssistService->apiResponse(404, 'No Such Appointment Request', compact('appointment_request_id'));
        }

//        if ($appointment_request->appointed_check_complete !== 0 || $appointment_request->appointed_check_complete == false) {
        if ($appointment_request->appointed_check_complete) {
            return $this->checkResponded($appointment_request_id, $appointment_request->appointed_check_result, $appointment_request->activity_id, $appointment_request->appointed_check_datetime, 'appointed');
        }
        if ($appointment_request->status !== 0) {
            return $this->checkResponded($appointment_request_id, $appointment_request->status, $appointment_request->activity_id, $appointment_request->accept_decline_datetime);
        }

        if ($appointment_request->offer_expiry_datetime < Carbon::now()) {
            return $this->checkExpired($appointment_request_id, $appointment_request->activity_id, $appointment_request->offer_expiry_datetime);
        }

        $selected_offer = collect(($appointment_request->valid_offers))->where('id', "=", $request->appointment_offer_id)->values()->first();
        if (!$selected_offer) {
            return $this->IFSPSOAssistService->apiResponse(406, 'Sorry, that offer ID is invalid. Please review valid offers.', compact('appointment_request_id'));
        }

        $input_ref = (new InputReference($request->description ?: 'Appointment Slot Check for ' . $appointment_request->activity_id, 'CHANGE', $appointment_request->dataset_id))->toJson();
        $offer_response_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, $request->appointment_offer_id);
        $payload = $this->AppointmentOfferResponsePayload($input_ref, $offer_response_payload);

        if ($request->send_to_pso) {


            $response = $this->IFSPSOAssistService->sendPayloadToPSO($payload, $this->token, $request->base_url, true);


            $summary_check = $response->collect()->first();
            if (!Arr::has($summary_check, 'Appointment_Summary')) {
                return $this->IFSPSOAssistService->apiResponse('404', 'Appointment Request does not exist', $payload);
            }
            $summary = collect($response->collect()->first()['Appointment_Summary']);

            $additional_data = [
                'description' => 'appointment_summary',
                'data' => [
                    'appointment_request_id' => $appointment_request_id,
                    'slot_is_available' => $summary['appointed'],
                    'full_summary' => $summary
                ]
            ];

            $this->appoint_offer($appointment_request, $input_ref['id'], $request->appointment_offer_id, $summary['appointed'], $payload);
            if ($summary['appointed'] === 'true') {
                return $this->IFSPSOAssistService->apiResponse(200, "Payload sent to PSO. Slot is Available", $payload, 'appointment_summary_slot_check', $additional_data);
            } else {
                return $this->IFSPSOAssistService->apiResponse(404, "Payload sent to PSO. Slot is Not Available", $payload, 'appointment_summary_slot_check', $additional_data);

            }
        }
        return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO.", $payload, 'appointed_check');

    }

    /**
     * @throws JsonException
     */
    private function appoint_offer(PSOAppointment $appointment_request, $id, $offer_id, $result, $payload)
    {
        $appointment_request->appointed_check_complete = 1;
        $appointment_request->appointed_check_offer_id = $offer_id;
        $appointment_request->appointed_check_result = $result === 'true' ? 1 : 0;
        $appointment_request->appointed_check_input_reference_id = $id;
        $appointment_request->appointed_check_datetime = Carbon::now()->toAtomString();
        $appointment_request->appointed_check_payload = json_encode($payload, JSON_THROW_ON_ERROR);
        $appointment_request->save();
    }

    private function checkResponded($appointment_request_id, $status, $activity_id, $datetime, $type = "accept_decline")
    {


        $some_details = compact('appointment_request_id', 'activity_id');

        if ($type === 'accept_decline') {
            $some_details["actioned_time"] = $datetime->diffForHumans();
            $some_details["status"] = $status === 1 ? "accepted" : "declined";
            $desc = 'The Offer Has Already Been Responded To';
        }
        if ($type === 'appointed') {
            $some_details["appointed_time"] = $datetime->diffForHumans();
            $some_details["status"] = $status === 1 ? "available" : "not available";
            $desc = 'Appointed Check Has Been Already Performed';
        }

        return $this->IFSPSOAssistService->apiResponse(
            409,
            $desc,
            $some_details,
            'additional_details');

    }

    private function checkExpired($appointment_request_id, $activity_id, $offer_expiry_datetime)
    {
        $some_details = [
            "appointment_request_id" => $appointment_request_id,
            "status" => 0,
            "activity_id" => $activity_id,
            "expired" => $offer_expiry_datetime->diffForHumans()
        ];

        return $this->IFSPSOAssistService->apiResponse(
            409,
            'The Offer Has Already Expired',
            $some_details,
            'additional_details');
    }


    public function declineAppointment(Request $request, $appointment_request_id) //: JsonResponse
    {

        // lookup the appointment request ID

        try {
            $appointment_request = PSOAppointment::where('id', $appointment_request_id)->firstOrFail();

        } catch (ModelNotFoundException) {
            return $this->IFSPSOAssistService->apiResponse(404, 'No Such Appointment Request', compact('appointment_request_id'));
        }

        if ($appointment_request->status !== 0) {
            return $this->checkResponded($appointment_request_id, $appointment_request->status, $appointment_request->activity_id, $appointment_request->accept_decline_datetime);
        }
        if ($appointment_request->offer_expiry_datetime < Carbon::now()) {
            return $this->checkExpired($appointment_request_id, $appointment_request->activity_id, $appointment_request->offer_expiry_datetime);
        }


        // lookup the activity

        $this->deleteActivity(
            $request->base_url,
            $request,
            $this->token,
            $appointment_request->activity_id,
            $request->account_id,
            $appointment_request->dataset_id
        );

        /* replaced this block with $this->deleteActivity
        $activity = new IFSPSOActivityService($request->base_url, $this->token, null, null, $request->account_id, true);
        $activity->getActivity($request, $appointment_request->activity_id, $appointment_request->dataset_id);

        if (!$activity->activityExists()) {
            return $this->IFSPSOAssistService->apiResponse(404, 'Activity does not exist in PSO', ['activity_id' => $request->activity_id]);
        }

        $activity_request = new Request(
            [
                'activity_id' => $activity->getActivityID(),
                'dataset_id' => $appointment_request->dataset_id,
                'base_url' => $request->base_url,
                'send_to_pso' => true
            ]);

        $activity->deleteActivity($activity_request, "deleting temp activity - declined appointments");
        */
        // decline the appointment
        $decline_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, -1, true);
        $input_ref = (new InputReference($request->description ?: 'Declining Appointments', 'CHANGE', $appointment_request->dataset_id))->toJson();
        $payload = $this->AppointmentOfferResponsePayload($input_ref, $decline_payload);

        // update the appointment_request in DB
        $this->accept_decline_appointment_request($appointment_request, $input_ref['id'], 2);

        return $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, 'appointment_offers_declined');
    }


    /**
     * @throws JsonException
     */
    public function acceptAppointment(Request $request, $appointment_request_id)//: JsonResponse
    {

        try {

            $appointment_request = PSOAppointment::where('id', $appointment_request_id)->firstOrFail();

        } catch (ModelNotFoundException) {
            return $this->IFSPSOAssistService->apiResponse(404, 'No Such Appointment Request', compact('appointment_request_id'));
        }

        if ($appointment_request->status !== 0) {
            return $this->checkResponded($appointment_request_id, $appointment_request->status, $appointment_request->activity_id, $appointment_request->accept_decline_datetime);
        }
        if ($appointment_request->offer_expiry_datetime < Carbon::now()) {
            return $this->checkExpired($appointment_request_id, $appointment_request->activity_id, $appointment_request->offer_expiry_datetime);
        }

        // check if the selected offer is in the valid list
//        return $appointment_request->valid_offers;
        $selected_offer = collect($appointment_request->valid_offers)->where('id', "=", $request->appointment_offer_id)->values()->first();
        if (!$selected_offer) {
            return $this->IFSPSOAssistService->apiResponse(406, 'Sorry, that offer ID is invalid . Please review valid offers . ', compact('appointment_request_id'));
        }


        // need to get sla_start and sla_end from the offer sent in
        $sla_start = $selected_offer->window_start_datetime;
        $sla_end = $selected_offer->window_end_datetime;


        // return values formatting
        $timezone = collect($appointment_request->input_request)->has('timezone') ? $appointment_request->input_request->timezone : config('pso-services.defaults.timezone');
        $assignment_start_raw = $selected_offer->prospective_allocation_start;
        $activity_duration = $appointment_request->input_request->duration;


        $assignment_finish = Carbon::parse($assignment_start_raw)->setTimezone($timezone)->addMinutes($activity_duration)->format('g:i A');
        $assignment_start = Carbon::parse($assignment_start_raw)->setTimezone($timezone)->format('g:i A');
        $prospective_resource = $selected_offer->prospective_resource_id;
        $selected_date = Carbon::parse($sla_start)->setTimezone($timezone)->format('l \\t\\h\\e jS \\of F Y');
        $selected_window = Carbon::parse($sla_start)->setTimezone($timezone)->format('g:i A') . " - " . Carbon::parse($sla_end)->setTimezone($timezone)->format('g:i A');
        $pso_allocation = $assignment_start . " - " . $assignment_finish;

        // also time to figure out what the new activity ID is
        $new_activity_id = Str::before($appointment_request->activity_id, config('pso-services.defaults.activity.appointment_booking_suffix'));
//        $new_activity_id = $appointment_request->activity_id;

        $activity_input_request = collect($appointment_request->input_request);
        $activity_input_request['sla_end'] = $sla_end;
        $activity_input_request['sla_start'] = $sla_start;
        $activity_input_request['sla_type_id'] = $request->sla_type_id;
        $activity_input_request['activity_id'] = $new_activity_id;
        $activity_input_request['status_id'] = 0;

        // update this to collection from request or from request to collection
//        $activity = new PSOActivity(new Request($activity_input_request->all()), false); // old

        $activity = new PSOActivity(json_decode(json_encode($activity_input_request->all(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR), false); // new


        $activity_payload_part = $activity->FullActivityObject();


        /* no longer needed, no temp activity
        // todo make this parameter based with the temp activity
        $this->deleteActivity(
            $request->base_url,
            $request,
            $this->token,
            $appointment_request->activity_id,
            $request->account_id,
            $appointment_request->dataset_id,
            true
        ); */


        // generate the new SLA
        // $new_sla = (new PSOActivitySLA($request->sla_type_id, $sla_start, $sla_end, $request->sla_priority, $request->sla_start_based))->toJson($new_activity_id);
        // send the SLA -- no don't send it yet, it needs to be in one payload because we have a new activity ID
//        $input_ref = (new InputReferenceBuilder($request->description ?: 'Updated Activity SLA', 'CHANGE', $request->dataset_id))->toJson();
//        $this->IFSPSOAssistService->processPayload(true, $this->ActivitySLAPayload($input_ref, $new_sla), $this->token, $request->base_url, 'updated_sla');

        // accept the slot
        $accept_payload = $this->AppointmentOfferResponsePayloadPart($appointment_request_id, $request->appointment_offer_id, true);
        $input_ref = (new InputReference($request->description ?: 'Accepted Appointment Slot for ' . $new_activity_id, 'CHANGE', $appointment_request->dataset_id))->toJson();
        $this->IFSPSOAssistService->processPayload(true, $this->AppointmentOfferResponsePayload($input_ref, $accept_payload), $this->token, $request->base_url, 'appointment_offer_accepted');

        // create the new activity
        // damn we need all the stuff, skills, regions, location etc. because we trashed the original
        $input_ref = (new InputReference('Appointed Activity Confirmed for ' . $new_activity_id, 'CHANGE', $appointment_request->dataset_id, $request->input_datetime))->toJson();

        $full_activity_payload = [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Activity' => $activity_payload_part['Activity'],
                'Activity_Skill' => $activity_payload_part['Activity_Skill'],
                'Activity_SLA' => $activity_payload_part['Activity_SLA'],
                'Activity_Status' => $activity_payload_part['Activity_Status'],
                'Location' => $activity_payload_part['Location'],
                'Location_Region' => $activity_payload_part['Location_Region'],
            ]
        ];

        $this->accept_decline_appointment_request(
            $appointment_request,
            $input_ref['id'],
            1,
            json_encode($selected_offer, JSON_THROW_ON_ERROR),
            $request->appointment_offer_id,
            $sla_start,
            $this->AppointmentOfferResponsePayload($input_ref, $accept_payload),
            $full_activity_payload);


// old version using processpayload -- moving to sendpayloadtoPSO
        //        return $this->IFSPSOAssistService->processPayload(
//            true,
//            $full_activity_payload,
//            $this->token,
//            $request->base_url,
//            'Appointed Activity Sent'
//        );
        $this->IFSPSOAssistService->sendPayloadToPSO($full_activity_payload, $this->token, $request->base_url);

        // trash the appointment SLA

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);
        $request->sla_type_id = 'APPOINTMENT';
        $request->activity_id = $new_activity_id;
        $request->priority = 2;
        $request->start_based = true;

        $activity->deleteSLA($request);
        return $this->IFSPSOAssistService->apiResponse(
            200,
            "Appointment Booked",
            $full_activity_payload,
            'original_payload',
            [
                'data' => [
                    'activity_id' => $new_activity_id,
                    'resource_id' => $prospective_resource,
                    'assignment_start' => $assignment_start,
                    'assignment_finish' => $assignment_finish,
                    'pso_allocation' => $pso_allocation,
                    'selected_date' => $selected_date,
                    'selected_window' => $selected_window
                ],
                'description' => 'bookingresponse'
            ]
        );

    }

    private function deleteActivity($base_url, $request, $token, $activity_id, $account_id, $dataset_id, $accept_decline = false)
    {

        $activity = new IFSPSOActivityService($base_url, $token, null, null, $account_id, true);
        $activity->getActivity($request, $activity_id, $dataset_id);

        if (!$accept_decline && !$activity->activityExists()) {
            // if it's accept/decline, ignore the check
            return $this->IFSPSOAssistService->apiResponse(404, 'Activity does not exist in PSO', ['activity_id' => $request->activity_id]);
        }

        // todo update this to collection from request
        $activity_request = new Request(
            [
                'activity_id' => $activity_id,
                'dataset_id' => $dataset_id,
                'base_url' => $base_url,
                'send_to_pso' => true
            ]);

        $desc = $accept_decline ? "accepted appointment" : "declined appointments";
        $activity->deleteActivity($activity_request, "deleting temp activity - " . $desc);

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
        return compact('appointment_request_id', 'appointment_offer_id', 'input_updated');
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
                'Location_Region' => $activity_payload['Location_Region'],
            ]
        ];
    }


    /**
     * @param array $appointment_request_part_payload
     * @param $input_request
     * @param array $payload
     * @param PSOActivity $activity
     * @param $dataset_id
     * @param $id
     * @param PromiseInterface|Response $response
     * @param $valid_offers
     * @param $invalid_offers
     * @param $best_offer
     * @param null $run_id
     * @return void
     * @throws JsonException
     */
    private function save_appointment_request(array $appointment_request_part_payload, $input_request, array $payload, PSOActivity $activity, $dataset_id, $id, PromiseInterface|Response $response, $valid_offers, $invalid_offers, $best_offer, $run_id = null): void
    {
        $appointment_request = new PSOAppointment();
        $appointment_request->id = $appointment_request_part_payload['id'];
        $appointment_request->appointment_request = json_encode($payload, JSON_THROW_ON_ERROR);
        $appointment_request->input_request = json_encode($input_request, JSON_THROW_ON_ERROR);
        $appointment_request->activity_id = $activity->getActivityID();
        $appointment_request->dataset_id = $dataset_id;
        $appointment_request->base_url = $input_request['base_url'];
        $appointment_request->input_reference_id = $id;
        $appointment_request->appointment_template_id = $appointment_request_part_payload['appointment_template_id'];
        $appointment_request->run_id = $run_id;
        $appointment_request->appointment_template_duration = $appointment_request_part_payload['appointment_template_duration'];
        $appointment_request->appointment_template_datetime = $appointment_request_part_payload['appointment_template_datetime'];
        $appointment_request->offer_expiry_datetime = $appointment_request_part_payload['offer_expiry_datetime'];
        $appointment_request->slot_usage_rule_id = Arr::has($appointment_request_part_payload, 'slot_usage_rule_id') ? $appointment_request_part_payload['slot_usage_rule_id'] : null;
        $appointment_request->appointment_response = json_encode($response->collect(), JSON_THROW_ON_ERROR);
        $appointment_request->valid_offers = json_encode($valid_offers, JSON_THROW_ON_ERROR);
        $appointment_request->invalid_offers = json_encode($invalid_offers, JSON_THROW_ON_ERROR);
        if ($best_offer) {
            $appointment_request->best_offer = $best_offer->get('prospective_resource_id') ? json_encode($best_offer, JSON_THROW_ON_ERROR) : json_encode('no valid offers returned', JSON_THROW_ON_ERROR);
        } else {
            json_encode('no valid offers returned', JSON_THROW_ON_ERROR);
        }
        if ($valid_offers) {
            $appointment_request->summary = $valid_offers->count() . ' valid offers out of ' . collect($response->collect()->first()['Appointment_Offer'])->count() . ' returned.';
        } else {
            $appointment_request->summary = 'no valid offers returned';
        }
        $appointment_request->total_offers_returned = collect($response->collect()->first()['Appointment_Offer'])->count();
        $appointment_request->total_valid_offers_returned = $valid_offers ? $valid_offers->count() : 0;
        $appointment_request->total_invalid_offers_returned = $invalid_offers ? $invalid_offers->count() : null;
//        $appointment_request->user_id = 'test';
        $appointment_request->save();
    }

    /**
     * @param PSOAppointment $appointment_request
     * @param $id
     * @param $status
     * @param null $accepted_offer
     * @param int $offer
     * @param null $accepted_offer_window_start_datetime
     * @param null $a_d_payload
     * @param null $b_a_payload
     * @return void
     * @throws JsonException
     */
    private function accept_decline_appointment_request(PSOAppointment $appointment_request, $id, $status, $accepted_offer = null, int $offer = 0, $accepted_offer_window_start_datetime = null, $a_d_payload = null, $b_a_payload = null): void
    {
        $appointment_request->status = $status;
        $appointment_request->accepted_offer = $accepted_offer;
        $appointment_request->accepted_offer_id = $offer;
        $appointment_request->accept_decline_input_reference_id = $id;
        $appointment_request->accept_decline_datetime = Carbon::now()->toAtomString();
        $appointment_request->accepted_offer_window_start_datetime = $accepted_offer_window_start_datetime;
        $appointment_request->accept_decline_payload = json_encode($a_d_payload, JSON_THROW_ON_ERROR);
        $appointment_request->book_appointment_payload = $b_a_payload ? json_encode($b_a_payload, JSON_THROW_ON_ERROR) : null;

        $appointment_request->save();
    }

    /**
     * @param $offer
     * @param null $id
     * @param null $timezone
     * @return Collection
     * this is a DRY method
     */
    public function getPut($offer, $id = null, $timezone = null): Collection
    {
        $newcollect = collect($offer)
            ->only('id', 'window_start_datetime', 'window_end_datetime', 'offer_value', 'prospective_resource_id', 'prospective_allocation_start')
            ->put('window_start_english', Carbon::parse($offer['window_start_datetime'])->setTimezone($timezone ?? config('pso-services.defaults.timezone'))->toDayDateTimeString())
            ->put('window_end_english', Carbon::parse($offer['window_end_datetime'])->setTimezone($timezone ?? config('pso-services.defaults.timezone'))->toDayDateTimeString())
            ->put('window_day_english', Carbon::parse($offer['window_start_datetime'])->setTimezone($timezone ?? config('pso-services.defaults.timezone'))->toFormattedDayDateString())
            ->put('window_start_time', Carbon::parse($offer['window_start_datetime'])->setTimezone($timezone ?? config('pso-services.defaults.timezone'))->format('g:i A'))
            ->put('window_end_time', Carbon::parse($offer['window_end_datetime'])->setTimezone($timezone ?? config('pso-services.defaults.timezone'))->format('g:i A'));

        if ($id !== null) {
            // an ID will only be sent on the valid offers object and not on the best offer object
            $newcollect->put('is_best_offer', $offer['id'] === $id);
        }

        return $newcollect;
    }

}
