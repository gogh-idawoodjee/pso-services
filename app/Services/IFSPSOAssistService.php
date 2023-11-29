<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Helpers\PSOHelper;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class IFSPSOAssistService extends IFSService
{

    private function SourceDataParameter($rota_id)
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
                'parameter_name' => 'rota_id',
                'parameter_value' => (string)$rota_id,
            ];
    }

    private function RotaToDSEPayload($dataset_id, $rota_id, $datetime, $include_broadcast, $broadcast_type, $broadcast_url, $desc): array
    {
        if (!$desc) {
            $desc = "Update Rota from " . $this->service_name;
        }
        $input_reference = (new InputReference(
            $desc,
            'CHANGE',
            $dataset_id,
            $datetime)
        )->toJson();


        $rota_to_dse_payload = collect([
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter(PSOHelper::RotaID($dataset_id, $rota_id)),
            ]
        ]);

        if ($include_broadcast) {
            $broadcast_payload = $this->BroadcastPayload($broadcast_type, $broadcast_url);
            $rota_to_dse_payload = collect($rota_to_dse_payload->first())->merge(['Broadcast' => $broadcast_payload['Broadcast']]);
            $rota_to_dse_payload = $rota_to_dse_payload->merge(['Broadcast_Parameter' => $broadcast_payload['Broadcast_Parameter']]);
            return ['dsScheduleData' => [$rota_to_dse_payload]];
        }

        return $rota_to_dse_payload->toArray();


    }

    public function sendRotaToDSE($dataset_id, $rota_id, $base_url, $date = null, $send_to_pso = null, $include_broadcast = null, $broadcast_type = null, $broadcast_url = null, $desc200 = null)//: JsonResponse
    {
        Log::debug('sending rota to dse, this is the service itself');

        $payload = $this->RotaToDSEPayload($dataset_id, $rota_id, $date, $include_broadcast, $broadcast_type, $broadcast_url, $desc200);

        return $this->processPayload($send_to_pso, $payload, $this->token, $base_url);

    }

    public function apiResponse($code, $description, $payload, $payload_desc = null, $additional_data = null): JsonResponse
    {
        // all other services will call this method for payloads
        if ($additional_data) {
            return response()->json([
                'status' => $code,
                'description' => $description,
                $additional_data['description'] => $additional_data['data'],
                $payload_desc ?: 'original_payload' => [$payload]
            ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }
        return response()->json([
            'status' => $code,
            'description' => $description,
            $payload_desc ?: 'original_payload' => [$payload]
        ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    private function SourceData(): array
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
            ];
    }

    public function BroadcastPayload($broadcast_type, $broadcast_url)
    {
        $broadcast_id = Str::orderedUuid()->getHex()->toString();
        return [
            'Broadcast' => [
                'active' => true,
                'allocation_type' => $broadcast_type ?: 8,
                'broadcast_type_id' => 'REST',
                'id' => $broadcast_id,
                'once_only' => false,
                'plan_type' => 'COMPLETE'
            ],
            'Broadcast_Parameter' => [
                [
                    'broadcast_id' => $broadcast_id,
                    'parameter_name' => 'mediatype',
                    'parameter_value' => 'application/json'
                ],
                [
                    'broadcast_id' => $broadcast_id,
                    'parameter_name' => 'url',
                    'parameter_value' => $broadcast_url
                ]
            ],
        ];
    }

    private function getSchedule($base_url, $dataset_id)
    {
        return IFSPSOScheduleService::getSchedule($base_url, $dataset_id, $this->token, false, false);
    }

    private function getScheduleData($schedule)
    {

        // collecting all existing schedule data required for the load
        $activities = [];
        $required_statuses = [];
        $required_skills = [];
        $required_slas = [];
        $required_locations = [];
        $required_location_regions = [];
        $schedule_events = [];
        $schedule_exception_responses = [];

        if (!$schedule->collect()) {
            return false;
        }
        $fullschedule = $schedule->collect()->first();
        // only run through the whole thing if we at least have an activity
        if (Arr::has($fullschedule, 'Activity')) {
            $activity = collect($fullschedule['Activity']);


            if ($activity->count()) {
                if ($activity->has('id')) {
                    $activities = [$activity];
                } else {
                    $activities = $activity;
                }

            }
            $activity_keys = collect($activities)->pluck('id');
            $activity_locations = collect($activities)->pluck('location_id');


            // then get Activity_Skill, Activity_Status, where activity_id in list of activities
            if (Arr::has($fullschedule, 'Activity_Skill')) {
                $activity_skill = collect($fullschedule['Activity_Skill']);
                if ($activity_skill->count()) {
                    if ($activity_skill->has('id')) {
                        $activity_skills = [$activity_skill];
                    } else {
                        $activity_skills = $activity_skill;
                    }
                }
                $activity_skills = collect($activity_skills);
                $required_skills = ['Activity_Skill' => $activity_skills->whereIn('activity_id', $activity_keys)->values()];

            }

            if (Arr::has($fullschedule, 'Schedule_Event')) {

                $schedule_event = collect($fullschedule['Schedule_Event']);
                if ($schedule_event->count()) {
                    if ($schedule_event->has('status_id')) {
                        $schedule_events = [$schedule_event];
                    } else {
                        $schedule_events = $schedule_event;
                    }
                }
                $schedule_events = collect($schedule_events);

            }

            if (Arr::has($fullschedule, 'Schedule_Exception_Response')) {

                $schedule_exception_response = collect($fullschedule['Schedule_Exception_Response']);
                if ($schedule_exception_response->count()) {
                    if ($schedule_exception_response->has('status_id')) {
                        $schedule_exception_responses = [$schedule_exception_response];
                    } else {
                        $schedule_exception_responses = $schedule_exception_response;
                    }
                }
                $schedule_exception_responses = collect($schedule_exception_responses);

            }


            if (Arr::has($fullschedule, 'Activity_Status')) {

                $activity_status = collect($fullschedule['Activity_Status']);
                if ($activity_status->count()) {
                    if ($activity_status->has('status_id')) {
                        $activity_statuses = [$activity_status];
                    } else {
                        $activity_statuses = $activity_status;
                    }
                }
                $activity_statuses = collect($activity_statuses);
                $required_statuses = $activity_statuses->whereIn('activity_id', $activity_keys)->values();
            }


            if (Arr::has($fullschedule, 'Activity_SLA')) {

                $activity_sla = collect($fullschedule['Activity_SLA']);
                if ($activity_sla->count()) {
                    if ($activity_sla->has('sla_type_id')) {
                        $activity_slas = [$activity_sla];
                    } else {
                        $activity_slas = $activity_sla;
                    }
                }
                $activity_slas = collect($activity_slas);
                $required_slas = $activity_slas->whereIn('activity_id', $activity_keys)->values();
            }

            if (Arr::has($fullschedule, 'Location')) {

                $location = collect($fullschedule['Location']);
                if ($location->count()) {
                    if ($location->has('id')) {
                        $locations = [$location];
                    } else {
                        $locations = $location;
                    }
                }
                $locations = collect($locations);
                $required_locations = $locations->whereIn('id', $activity_locations)->values();
            }

            if (Arr::has($fullschedule, 'Location_Region')) {

                $location_region = collect($fullschedule['Location_Region']);
                if ($location_region->count()) {
                    if ($location_region->has('location_id')) {
                        $location_regions = [$location_region];
                    } else {
                        $location_regions = $location_region;
                    }
                }
                $location_regions = collect($location_regions);
                $required_locations = $location_regions->whereIn('activity_id', $activity_locations)->values();
            }
        }
        return [
            'Activity' => $activities,
            'Activity_Status' => $required_statuses,
            'Activity_SLA' => $required_slas,
            'Activity_Skill' => $required_skills,
            'Location' => $required_locations,
            'Location_Region' => $required_location_regions,
            'Schedule_Event' => $schedule_events,
            'Schedule_Exception_Response' => $schedule_exception_responses
        ];
    }

    private function initializePSOPayload(Request $request)
    {

        $description = $request->description ?: 'Init via ' . $this->service_name;
        $request->keep_pso_data === true ? $description .= ' (Keeping PSO Data by Request)' : '';
        $datetime = $request->datetime ?: Carbon::now()->toAtomString();
        $dse_duration = PSOHelper::setPSODurationDays($request->dse_duration); // this doesn't need the helper elf we're expecting a solid number of days only here
        if ($request->appointment_window) {
            $appointment_window = PSOHelper::setPSODurationDays($request->appointment_window);
        } else {
            $appointment_window = null;
        }
        $process_type = $request->process_type ?: config('pso-services.defaults.process_type');
        $rota_id = PSOHelper::RotaID($request->dataset_id, $request->rota_id);

        $input_ref = (
        new InputReference(
            $description,
            'LOAD',
            $request->dataset_id,
            $datetime,
            $dse_duration,
            $process_type,
            $appointment_window
        ))->toJson();


        $init_payload = collect([
            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
            'Input_Reference' => $input_ref,
            'Source_Data' => $this->SourceData(),
            'Source_Data_Parameter' => $this->SourceDataParameter($rota_id),
        ]);


        if ($request->include_broadcast) {
            $broadcast_payload = $this->BroadcastPayload($request->broadcast_type, $request->broadcast_url);
            $init_payload = collect($init_payload)->merge(['Broadcast' => $broadcast_payload['Broadcast']]);
            $init_payload = $init_payload->merge(['Broadcast_Parameter' => $broadcast_payload['Broadcast_Parameter']]);

        }

        if ($request->keep_pso_data && $this->getSchedule($request->base_url, $request->dataset_id)) {

            $init_payload = $init_payload->merge($this->getScheduleData($this->getSchedule($request->base_url, $request->dataset_id)));
        }


        return ['dsScheduleData' => [$init_payload]];
    }

    public function InitializePSO(Request $request)
    {

        $payload = $this->initializePSOPayload($request);
        $desc = 'Initialize via ' . $this->service_name . ($request->keep_pso_data === true ? ' Keeping PSO Data' : '');

        return $this->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $desc);
    }

    public function getUsageData($request)
    {

        $mindate = $request->mininum_date ?: Carbon::now()->format('Y-m-d');
        $maxdate = $request->maximum_date ?: Carbon::now()->add(1, 'day')->format('Y-m-d');

        $usage = Http::withHeaders([
            'apiKey' => $this->token
        ])->get(
            $request->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/usage',
            [
                'minimumDateTime' => $mindate,
                'maximumDateTime' => $maxdate
            ]);
        $keys = collect($usage->collect()->first())->groupBy('DatasetId')->keys();


        if (!$keys->contains($request->dataset_id)) {
            return $this->apiResponse(404, 'Dataset not found in this environment', ['dataset_id_requested' => $request->dataset_id, 'datasets_available' => $keys]);
        }

        if ($usage->collect()->first()) {

            $usage_values = collect($usage->collect()->first())->map(function ($item) {

                $type = match ($item['ScheduleDataUsageType']) {
                    0 => 'Resource_Count',
                    1 => 'Activity_Count',
                    2 => 'DSE_Window',
                    3 => 'ABE_Window',
                    4 => 'Dataset_Count',
                };

                return collect($item)->put('count_type', $type);
            })->mapToGroups(fn($item) => [
                $item['DatasetId'] => $item
            ]);

            $grouped_values = [];
            foreach ($usage_values as $dataset => $value) {
                $grouped_values[$dataset] = collect($value)->mapToGroups(fn($item) => [
                    $item['count_type'] => $item
                ]);
            }

            $formatted_data = [];

            foreach ($grouped_values[$request->dataset_id] as $counttype) {
                foreach ($counttype as $countdata) {
                    $formatted_data[$countdata['count_type']][] = [
                        'date' => config('pso-services.settings.use_system_date_format') ? Carbon::createFromDate($countdata['DatetimeStamp'])->toDateTimeString() : Carbon::createFromDate($countdata['DatetimeStamp'])->calendar(),
                        'count' => $countdata['Value']
                    ];

                }
            }

            return $this->apiResponse(
                200,
                'Usage Data',
                [$request->dataset_id => $formatted_data],
                'usage_data'
            );
        }

        return $this->apiResponse(
            418,
            "I'm not actually a teapot but no information was available from PSO",
            ['you asked for usage data' => ['for dataset' => $request->dataset_id, 'from' => $request->base_url]]
        );

    }

    public function sendPayloadToPSO($payload, $token, $base_url, $requires_pso_response = false)
    {
        $endpoint_segment = $requires_pso_response ? 'appointment' : 'data';

        try {
            return Http::timeout(PSOHelper::GetTimeOut())
                ->withHeaders(['apiKey' => $token])
                ->connectTimeout(PSOHelper::GetTimeOut())
                ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/' . $endpoint_segment, $payload);
        } catch (ConnectionException) {
            return response('failed', 500);
        }
    }

    public function RAMUpdatePayload($dataset_id, $description): array
    {
        return [
            'organisation_id' => '2',
            'dataset_id' => $dataset_id,
            'user_id' => $this->service_name . ' user',
            'ram_update_type_id' => 'CHANGE',
            'is_master_data' => true,
            'description' => $description,
            'requesting_app_instance_id' => $this->service_name
        ];
    }

    public function processPayload($send_to_pso, $payload, $token, $base_url, $desc_200 = null, $requires_rota_update = false, $dataset_id = null, $rota_id = null)
    {
        if ($send_to_pso) {

            $response = $this->sendPayloadToPSO($payload, $token, $base_url);

            if ($response->json('InternalId') > -1) {
                // update the rota
                if ($requires_rota_update) {
                    $this->sendRotaToDSE(
                        $dataset_id,
                        $rota_id,
                        $base_url,
                        null,
                        true
                    );
                }
                // send the good response
                return $this->apiResponse(200, ("Payload successfully sent to PSO." . ($desc_200 ? ' ' . $desc_200 : $desc_200)), $payload);
            }

            if ($response->serverError() || $response->json('InternalId') === "-1") {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('Code') === 401 || $response->status() === 401) {
                return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }

            if ($response->status() === 500) {
                return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
            }
            return $this->apiResponse(418, "None of the above", $payload, null, ['description' => 'PSO Response', 'data' => $response->object()]);
        }

        return $this->apiResponse(202, "Successful but payload not sent to PSO by choice", $payload);

    }
}
