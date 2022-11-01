<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IFSPSOResourceService extends IFSService
{

    private Collection $pso_resource;
    private array $utilization;
    private $events;
    private $shifts;
    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function getResource($resource_id, $dataset_id, $base_url): Collection
    {

        try {
            $pso_resource = Http::withHeaders(['apiKey' => $this->token])
                ->timeout(5)
                ->connectTimeout(5)
                ->get($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/resource?includeOutput=true&datasetId=' . urlencode($dataset_id) . '&resourceId=' . $resource_id);
        } catch (ConnectionException) {
            return collect('failed pretty hard');
        }

        $this->pso_resource = collect($pso_resource->collect()->first());
        return $this->pso_resource;

    }

    public function getResourceEvents()
    {
        if (isset($this->pso_resource['Schedule_Event'])) {
            if (isset($this->pso_resource['Schedule_Event']['id'])) {
                $this->events[] = $this->pso_resource['Schedule_Event'];
            } else {
                $this->events = $this->pso_resource['Schedule_Event'];
            }
        } else {
            $this->events = [];
        }

        return $this->events;
    }

    public function getResourceShiftsRaw()
    {
        $this->getShifts();
        return $this->shifts;
    }

    public function getResourceShiftsFormatted(): Collection
    {
        $this->getShifts();

        return collect($this->shifts)->map(function ($item) {
            $shiftdate = Carbon::createFromDate($item['start_datetime'])->toDateString();
            $starttime = Carbon::createFromDate($item['start_datetime'])->format('h:i');
            $endtime = Carbon::createFromDate($item['end_datetime'])->format('h:i');
            $times = $starttime . ' - ' . $endtime;
            $difference = Carbon::createFromDate($item['start_datetime'])->diffInHours(Carbon::createFromDate($item['end_datetime']));

            $shifts = collect($item)
                ->put('shift_date', $shiftdate)
                ->put('shift_times', $times)
                ->put('shift_duration', $difference);

            if (!isset($item['manual_scheduling_only'])) {
                $shifts->put('manual_scheduling_only', false);
            } else {
                $shifts->put('manual_scheduling_isset', 'checked');
            }

            $shifts->pull('start_datetime');
            $shifts->pull('end_datetime');
            $shifts->pull('actual');
            $shifts->pull('split_allowed');
            $shifts->pull('resource_id');

            return $shifts;
        });
    }

    public function getResourceLocations()
    {
        if (isset($this->pso_resource['Location'])) {
            if (isset($this->pso_resource['Location']['id'])) {
                $this->events[] = $this->pso_resource['Location'];
            } else {
                $this->events = $this->pso_resource['Location'];
            }
        } else {
            $this->events = [];
        }

        return $this->events;
    }

    public function getResourceUtilization(): array
    {
        if (isset($this->pso_resource['Plan_Route'])) {
            if (isset($this->pso_resource['Plan_Route']['plan_id'])) {
                $this->utilization['dates'][] = ['date' => $this->pso_resource['Plan_Route']['shift_start_datetime']];
                $this->utilization['utilization'][] = ['utilization' => $this->pso_resource['Plan_Route']['utilisation']];
                $this->utilization['travel'][] = ['travel' => $this->pso_resource['Plan_Route']['average_travel_time']];
            } else {

                $this->utilization['dates'] = collect($this->pso_resource['Plan_Route'])->map(function ($item) {
                    return ['date' => Carbon::create($item['shift_start_datetime'])->toFormattedDateString()];
                });
                $this->utilization['utilization'] = collect($this->pso_resource['Plan_Route'])->map(function ($item) {
                    return ['utilization' => $item['utilisation']];
                });
                $this->utilization['travel'] = collect($this->pso_resource['Plan_Route'])->map(function ($item) {
                    return ['travel' => CarbonInterval::make(new DateInterval($item['average_travel_time']))->i];
                });
            }
        }

        return $this->utilization;
    }

    public function getScheduleableResources($request): Collection
    {

        $schedule = new IFSPSOScheduleService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        $overall_schedule = collect($schedule->getSchedule($request->dataset_id, $request->base_url)->collect());

        $resources = collect($overall_schedule->get('Resources'));
        $shifts = collect($overall_schedule->get('Plan_Route'))->groupBy('resource_id');
        $events = collect($overall_schedule->get('Schedule_Event'));
        // not currently needed
        // just adds the resource_id property to teh resource object
//        $mystuff = collect($resources)->map(function ($item, $key) {
//            return collect($item)->put('resource_id', $item['id']);
//        });


        if (!Arr::has($events, 'id')) {
            $events = collect($events)->mapToGroups(function ($item) {
                return [$item['resource_id'] => [
                    'id' => $item['id'],
                    'event_type_id' => $item['event_type_id'],
                    'date_time_stamp' => $item['date_time_stamp'],
                    'event_date_time' => $item['event_date_time'],
                ]];
            });
        }


//        if (!Arr::has($events, 'id')) {
//            $events = $events->keyBy('resource_id');
//        }

        $plans = collect($overall_schedule->get('Plan_Resource'))->keyBy('resource_id');
        return collect($resources)->map(function ($item) use ($events) {
            // how do we do this if it's only one event?
            if (isset($events[$item['id']])) {
                return collect($item)->put('events', $events[$item['id']]);
            } else {
                return $item;
            }
        })->map(function ($item) use ($shifts, $plans) {
            return collect($item)
                ->put('route', $plans[$item['id']])
                ->put('shift count', count($shifts[$item['id']]))
                ->put('shift_max', collect($shifts[$item['id']])->max('shift_start_datetime'))
                ->put('shift_min', collect($shifts[$item['id']])->min('shift_start_datetime'));
        });

    }

    public function setEvent(Request $event_data, $resource_id): JsonResponse
    {

        $requestId = (string)Str::uuid();
        Log::channel('papertrail')->info(['request_input' => ['request_id' => $requestId, 'payload' => $event_data->all()]]);

        // now we need to figure out if we need to auth or not // really this will have to be done at the controller to initialize this instance of the service

        // build the JSON for the schedule event itself
        $schedule_event = $this->ScheduleEventPayloadPart($event_data->event_type, $resource_id);
        $payload = $this->ScheduleEventPayload($event_data->dataset_id, $schedule_event);


        if ($event_data->send_to_pso) {

            Log::channel('papertrail')->info(['request_output' => ['request_id' => $requestId, 'payload' => $payload]]);
            $response = $this->sendPayloadToPSO($payload, $this->token, $event_data->base_url);

            if ($response->serverError()) {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('InternalId') == "-1") {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('InternalId') != "-1") {
                return $this->apiResponse(200, "Payload sent to PSO", $payload);
            }

            if ($response->json('Code') == 401) {
                return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }

            if ($response->status() == 500) {
                return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
            }

            if ($response->status() == 401) {
                return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }
        } else {
            return $this->apiResponse(202, "Payload not sent to PSO", $payload);
        }

        Log::channel('papertrail')->info(['request_output' => ['request_id' => $requestId, 'payload' => $payload]]);
    }


    private function ScheduleEventPayload($dataset_id, $schedule_event_payload)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => ('http://360Scheduling.com/Schema/dsScheduleData.xsd'),
                'Input_Reference' => $this->IFSPSOAssistService->InputReferenceData("Set Resource Event", $dataset_id, "CHANGE", null),
                'Schedule_Event' => $schedule_event_payload,

            ]
        ];
    }

    public function ScheduleEventPayloadPart($event_type, $resource_id): array
    {
        return
            [
                'id' => Str::orderedUuid()->getHex()->toString(),
                'date_time_stamp' => Carbon::now()->toAtomString(),
                'event_date_time' => Carbon::now()->toAtomString(),
                'event_type_id' => strtoupper($event_type),
                'resource_id' => "$resource_id"
            ];
    }

    public function setManualScheduling($shift_data)
    {

        $shift_set = $this->getResourceShiftsRaw();

        // build the json for the RAM_Rota_Item
        // the first param is looking at the list of shifts and finding the details on the one we're modifying
        $ram_rota_item_payload = $this->RAMRotaItemPayload(collect(collect($shift_set)->firstWhere('id', $shift_data->shift_id)), $shift_data->rota_id, $shift_data->turn_manual_scheduling_on, $shift_data->shift_type, "Manual Scheduling Only set to " . ($shift_data->turn_manual_scheduling_on ? "ON" : "OFF") . " by the thingy tool.(" . Carbon::now()->toDateTimeString() . ")");
        $ram_update_payload = $this->RAMUpdatePayload($shift_data->dataset_id, "Manual Scheduling Only set to " . ($shift_data->turn_manual_scheduling_on ? "ON" : "OFF") . " by the thingy tool");

        // now we build the payload and send the stuff send that stuff
        $payload = $this->RAMRotaItemUpdatePayload($ram_update_payload, $ram_rota_item_payload);
        if ($shift_data->send_to_pso) {
            $response = $this->sendPayloadToPSO($payload, $this->token, $shift_data->base_url);

            // do the following only if it's not a 500 series
            if ($response->successful()) {
                // todo clean this stuff up
                // todo, we can actually do a get on the resource shift again, do a compare on the description and compare to the payload; if it's the same description, then we know for sure it worked

                if ($response->json('InternalId') == "0") {
                    // then we send a Rota Update, so we can see the changes
                    // but maybe only do this if the payload above doesn't fail?
                    $this->IFSPSOAssistService->sendRotaToDSEPayload(
                        $shift_data->dataset_id,
                        $shift_data->rota_id,
                        $this->token,
                        $shift_data->base_url,
                        null,
                        true
                    );
//                    $this->sendRotaToDSEPayload($shift_data->dataset_id, $shift_data->rota_id, $this->token, $shift_data->base_url);
                    return $this->apiResponse(200, "Rota Item Updated", $payload);
                }

                if ($response->json('Code') == 26) {
                    return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
                }

                if ($response->json('Code') == 401) {
                    return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
                }
            } else {
                if ($response->json('Code') == 26) {
                    return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
                }

                if ($response->json('Code') == 401) {
                    return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
                }
                return $this->apiResponse(500, "Some issues sending the payload", $payload);
            }
        } else {
            return $this->apiResponse(202, "Payload not sent to PSO - if you see a lot of nulls, double check your shift_id. If you want to send this to PSO, add send_to_pso = true in your input.", $payload);
        }

    }

    private function apiResponse($code, $description, $payload): JsonResponse
    {
        return response()->json([
            'status' => $code,
            'description' => $description,
            'original_payload' => [$payload]
        ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);

    }


    private function sendPayloadToPSO($payload, $token, $base_url)
    {
        // todo this should go into the helper elf as well
        return Http::timeout(5)
            ->withHeaders(['apiKey' => $token])
            ->connectTimeout(5)
            ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data', $payload);
    }

    private function RAMRotaItemUpdatePayload($ram_update_payload, $rota_item_payload)
    {
        return [
            'DsModelling' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                'RAM_Update' => $ram_update_payload,
                'RAM_Rota_Item' => $rota_item_payload
            ]
        ];

    }

    private function RAMUpdatePayload($dataset_id, $description): array
    {
        return [
            'organisation_id' => '2',
            'dataset_id' => $dataset_id,
            'user_id' => 'thingy user',
            'ram_update_type_id' => 'CHANGE',
            'is_master_data' => true,
            'description' => $description
        ];

    }


    private function RAMRotaItemPayload($rawshift, $rota_id, $turn_manual_scheduling_on, $shift_type, $description): array
    {
        return [
            'id' => $rawshift->get('id'),
            'ram_rota_id' => "$rota_id",
            'manual_scheduling_only' => $turn_manual_scheduling_on,
            'ram_resource_id' => $rawshift->get('resource_id'),
            'start_datetime' => $rawshift->get('start_datetime'),
            'end_datetime' => $rawshift->get('end_datetime'),
            'ram_shift_category_id' => "$shift_type",
            'description' => "$description"
        ];
    }

    private function getShifts(): void
    {
        if (isset($this->pso_resource['Shift'])) {
            if (isset($this->pso_resource['Shift']['id'])) {
                $this->shifts[] = $this->pso_resource['Shift'];
            } else {
                $this->shifts = $this->pso_resource['Shift'];
            }
        } else {
            $this->shifts = [];
        }
    }

    public function createUnavailability(Request $request, $resource_id): JsonResponse
    {

        $time_pattern_id = Str::uuid()->getHex();
        $duration = 'PT' . $request->duration . 'H';
        $tz = null;
        if ($request->time_zone) {
            $tz = '+' . $request->time_zone . ':00';
            if ($request->time_zone < 10 && $request->time_zone > -10) {
                $tz = $request->time_zone < 0 ? '-0' . abs($request->time_zone) . ':00' : '+0' . abs($request->time_zone) . ':00';
            }
        }

        $base_time = $request->base_time . ':00' . $tz;

        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, 'Create Unavailability from the Thingy');
        $ram_unavailability_payload = $this->RAMUnavailabilityPayloadPart($resource_id, $time_pattern_id, $request->category_id, $request->description);
        $ram_time_pattern_payload = $this->RAMTimePatternPayload($time_pattern_id, $base_time, $duration);

        // send to PSO if needed
        if ($request->send_to_pso) {

            // if successful, send a rota update

            $this->IFSPSOAssistService->sendRotaToDSEPayload(
                $request->dataset_id,
                $request->rota_id,
                $this->token,
                $request->base_url,
                null,
                true
            );

        }

        return $this->apiResponse(202, 'Unavailability not sent to PSO', $this->RAMUnavailabilityPayload($ram_update_payload, $ram_unavailability_payload, $ram_time_pattern_payload));


    }

    private function RAMUnavailabilityPayloadPart($resource_id, $time_pattern_id, $category_id, $description): array
    {
        return [
            'id' => Str::uuid()->getHex(),
            'ram_time_pattern_id' => $time_pattern_id,
            'ram_resource_id' => $resource_id,
            'ram_unavailability_category_id' => "$category_id",
            'description' => "$description"
        ];
    }

    private function RAMTimePatternPayload($time_pattern_id, $base_time, $duration): array
    {
        return [
            'id' => $time_pattern_id,
            'base_time' => $base_time,
            'duration' => $duration
        ];
    }

    private function RAMUnavailabilityPayload($ram_update_payload, $ram_unavailability_payload, $ram_time_pattern_payload): array
    {
        return [
            'DsModelling' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                'RAM_Update' => $ram_update_payload,
                'RAM_Unavailability' => $ram_unavailability_payload,
                'RAM_Time_Pattern' => $ram_time_pattern_payload
            ]
        ];

    }

}
