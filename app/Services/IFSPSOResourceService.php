<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSODeleteObject;
use App\Helpers\Helper;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
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

    public function getResourceForWebApp($resource_id, $dataset_id, $base_url)
    {

        if (!$this->ResourceExists())
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Resource does not exist', ['resource_id' => $resource_id]);

        $payload = [
            'resource' => [
                'raw' => $this->getResource($resource_id, $dataset_id, $base_url), // todo clean this up, make it look nicer and more formatted
                'utilization' => $this->getResourceUtilization(),
                'events' => $this->getResourceEvents(),
                'locations' => $this->getResourceLocations(),
                'shifts' => $this->getResourceShiftsFormatted(),
            ]
        ];

        return response($payload, 200)
            ->header('Content-Type', 'application/json');


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

        $overall_schedule = collect($schedule->getScheduleAsCollection($request->dataset_id, $request->base_url)->collect());

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

        $this->getResource($resource_id, $event_data->dataset_id, $event_data->base_url);

        $schedule_event = $this->ScheduleEventPayloadPart($event_data->event_type, $resource_id, $event_data->event_date_time);
        $payload = $this->ScheduleEventPayload($event_data->dataset_id, $schedule_event);

        if (!$this->ResourceExists() && config('pso-services.settings.validate_object_existence')) {
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Resource does not exist', $payload);
        }

        return $this->IFSPSOAssistService->processPayload(
            $event_data->send_to_pso,
            $payload,
            $this->token,
            $event_data->base_url,
            'Event Set and Rota Updated',
            true,
            $event_data->dataset_id,
            $event_data->rota_id

        );
    }


    private function ScheduleEventPayload($dataset_id, $schedule_event_payload)
    {
        $input_reference = (new InputReference("Set Resource Event",
            'CHANGE',
            $dataset_id))->toJson();


        return [
            'dsScheduleData' => [
                '@xmlns' => ('http://360Scheduling.com/Schema/dsScheduleData.xsd'),
                'Input_Reference' => $input_reference,
                'Schedule_Event' => $schedule_event_payload,

            ]
        ];
    }

    private function ScheduleEventPayloadPart($event_type, $resource_id, $event_date_time): array
    {
        return
            [
                'id' => Str::orderedUuid()->getHex()->toString(),
                'date_time_stamp' => Carbon::now()->toAtomString(),
                'event_date_time' => $event_date_time ?: Carbon::now()->toAtomString(),
                'event_type_id' => strtoupper($event_type),
                'resource_id' => "$resource_id"
            ];
    }

    public function setManualScheduling($shift_data, $resource_id)
    {

        if (!$this->ResourceExists()) return $this->IFSPSOAssistService->apiResponse(404, 'Specified Resource does not exist', ['shift_id' => $shift_data->shift_id, 'resource_id' => $resource_id], 'submitted_data');

        $shift_set = $this->getResourceShiftsRaw();

        if (collect(collect($shift_set)->firstWhere('id', $shift_data->shift_id))->isEmpty()) {
            // don't bother doing anything if the shift doesn't exist
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Shift ID does not exist', ['shift_id' => $shift_data->shift_id, 'resource_id' => $resource_id], 'submitted_data');
        }

        // build the json for the RAM_Rota_Item
        // the first param is looking at the list of shifts and finding the details on the one we're modifying
        $description = "Manual Scheduling Only set to " . ($shift_data->turn_manual_scheduling_on ? "ON" : "OFF") . " by the thingy tool.(" . Carbon::now()->toDateTimeString() . ")";
        $ram_rota_item_payload = $this->RAMRotaItemPayload(collect(collect($shift_set)->firstWhere('id', $shift_data->shift_id)), $shift_data->rota_id, $shift_data->turn_manual_scheduling_on, $shift_data->shift_type, $description);
        $ram_update_payload = $this->RAMUpdatePayload($shift_data->dataset_id, "Manual Scheduling Only set to " . ($shift_data->turn_manual_scheduling_on ? "ON" : "OFF") . " by the thingy tool");

        // now we build the payload and send the stuff send that stuff
        $payload = $this->RAMRotaItemUpdatePayload($ram_update_payload, $ram_rota_item_payload);

        // do the check if we're sending to PSO
        if ($shift_data->send_to_pso) {

            $this->IFSPSOAssistService->processPayload(
                $shift_data->send_to_pso,
                $payload,
                $this->token,
                $shift_data->base_url,
                'Rota Item Updated',
                true,
                $shift_data->dataset_id,
                $shift_data->rota_id
            );

            // get the resource again
            $resource_init = new IFSPSOResourceService($shift_data->base_url, $this->token, null, null, null, true);
            $resource_init->getResource($resource_id, $shift_data->dataset_id, $shift_data->base_url);
            $fresh_shifts = $resource_init->getResourceShiftsRaw();
            $shift_in_question = collect(collect($fresh_shifts)->firstWhere('id', $shift_data->shift_id));

            // compare the shift
            if ($description == $shift_in_question['description']) {
                // if the description matches in the GET we can be certain it worked
                return $this->IFSPSOAssistService->apiResponse(200, 'Rota Item Updated and Validated', $payload);

            }
        }

        return $this->IFSPSOAssistService->processPayload(
            $shift_data->send_to_pso,
            $payload,
            $this->token,
            $shift_data->base_url,
            'Rota Item Updated',
            true,
            $shift_data->dataset_id,
            $shift_data->rota_id
        );

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

        $this->shifts = [];
        if (isset($this->pso_resource['Shift'])) {
            if (isset($this->pso_resource['Shift']['id'])) {
                $this->shifts[] = $this->pso_resource['Shift'];
            } else {
                $this->shifts = $this->pso_resource['Shift'];
            }
        }
    }

    public function createUnavailability(Request $request, $resource_id): JsonResponse
    {

        $time_pattern_id = Str::uuid()->getHex();
        $duration = Helper::setPSODuration($request->duration);

        $tz = Helper::setTimeZone($request->time_zone);

        $base_time = $request->base_time . ':00' . $tz;

        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, 'Create Unavailability from the Thingy');
        $ram_unavailability_payload = $this->RAMUnavailabilityPayloadPart($resource_id, $time_pattern_id, $request->category_id, $request->description);
        $ram_time_pattern_payload = $this->RAMTimePatternPayload($time_pattern_id, $base_time, $duration);
        $payload = $this->RAMUnavailabilityPayload($ram_update_payload, $ram_unavailability_payload, $ram_time_pattern_payload);

        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso,
            $payload,
            $this->token,
            $request->base_url,
            'Unavailability sent to PSO',
            true,
            $request->dataset_id,
            $request->rota_id
        );

    }


    public function updateUnavailability(Request $request, $unavailability_id)//: JsonResponse
    {


        $unavailabilities = [$unavailability_id];
        if ($request->unavailabilities) {
            $unavailabilities = collect($request->unavailabilities)->push($unavailability_id);
        }


        $schedule = IFSPSOScheduleService::getSchedule($request->base_url, $request->dataset_id, $this->token);

        if (!$schedule) {
            return $this->IFSPSOAssistService->apiResponse(406, 'Something Failed Getting the Schedule, double check your dataset', $request->all());
        }


        if (Arr::has($schedule->collect()->first(), 'Activity') && Arr::has($schedule->collect()->first(), 'Allocation')) {
            $grouped_activities = collect($schedule->collect()->first()['Activity'])->mapWithKeys(function ($activity) {
                return [$activity['id'] => $activity];
            })->only($unavailabilities);


            $grouped_allocations = collect($schedule->collect()->first()['Allocation'])->mapWithKeys(function ($allocation) {
                return [$allocation['activity_id'] => $allocation];
            })->only($unavailabilities);

            if ($grouped_activities->count() == 0 || $grouped_allocations->count() == 0) {
                // if none of those exist in the schedule return a 404
                return $this->IFSPSOAssistService->apiResponse(404, 'no NAs found', ['NAs sent' => $unavailabilities]);
            }
        } else {
            return $this->IFSPSOAssistService->apiResponse(404, 'Schedule is Pretty Empty', ['NAs sent' => $unavailabilities]);
        }

        // all these NAs will share a single time pattern based on the input (if there is one)
        $time_pattern_id = Str::uuid()->getHex();

        $duration = $request->duration ? Helper::setPSODuration($request->duration) : $grouped_allocations->first()['duration'];

        $tz = Helper::setTimeZone($request->time_zone, true, $grouped_allocations);

        $category_id = $request->category_id ?: $grouped_activities->first()['activity_type_id'];
        $description = ($request->description ?: $grouped_activities->first()['description']) . ' - Updated from the thingy on ' . Carbon::now()->toDayDateTimeString();

        $base_time = ($request->base_time ? $request->base_time . ':00' : Str::of($grouped_allocations->first()['activity_start'])->substr(1, 19)) . $tz;
        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, ($grouped_activities->count() > 0 ? 'Mass ' : '') . 'Update Unavailability from the Thingy');
        $ram_time_pattern_payload = $this->RAMTimePatternPayload($time_pattern_id, $base_time, $duration);
        $ram_unavailability_payload = [];
        foreach ($grouped_activities as $na) {
            $ram_unavailability_payload[] = $this->RAMUnavailabilityPayloadPart(
                $grouped_allocations[$na['id']]['resource_id'], $time_pattern_id, $category_id, $description);
        }

        $payload = $this->RAMUnavailabilityPayload($ram_update_payload, $ram_unavailability_payload, $ram_time_pattern_payload);

        $desc200 = (count($ram_unavailability_payload) > 1 ? count($ram_unavailability_payload) . ' Unavailabilities' : count($ram_unavailability_payload) . ' Unavailability') . ' updated';

        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso,
            $payload,
            $this->token,
            $request->base_url,
            $desc200,
            true,
            $request->dataset_id,
            $request->rota_id
        );

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

    public function DeleteUnavailability(Request $request): JsonResponse
    {
        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, 'Deleted Unavailability from the Thingy');

        //        $ram_data_update = $this->RAMDataDeletePayloadPart('RAM_Unavailability', $request->unavailability_id);
        $ram_data_update = (new PSODeleteObject(
            'RAM_Unavailability', 'id', '$request->unavailability_id',
            null, null,
            null, null,
            null, null,
            true)
        )->toJson();

        $payload = $this->RAMDataDeletePayload($ram_update_payload, $ram_data_update);

        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso,
            $payload,
            $this->token,
            $request->base_url,
            'Unavailability (probably) deleted',
            true,
            $request->dataset_id,
            $request->rota_id
        );

    }

    private function RAMDataDeletePayload($ram_update_payload, $ram_data_update): array
    {
        return [
            'DsModelling' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                'RAM_Update' => $ram_update_payload,
                'RAM_Data_Update' => $ram_data_update

            ]
        ];

    }

    private function ResourceExists()
    {
        if (!Arr::has($this->pso_resource, 'Resources')) {
            return false;
        }
        return true;
    }

}
