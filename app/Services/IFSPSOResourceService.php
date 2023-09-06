<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSODeleteObject;
use App\Classes\PSOResource;
use App\Helpers\PSOHelper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use DateInterval;
use Exception;

use Faker\Factory;
use GoogleMaps;
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
    private array $events;
    private array $shifts;
    private IFSPSOAssistService $IFSPSOAssistService;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);
        $this->pso_resource = collect();

    }

    /**
     * @throws Exception
     */
    public function getResourceForWebApp($resource_id, $dataset_id, $base_url)
    {

        $resource_raw = $this->getResource($resource_id, $dataset_id, $base_url);

        if (!$this->ResourceExists()) {
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Resource does not exist', compact('resource_id'));
        }

        $resource = collect($resource_raw['Resources']);

        $resource_type = collect($resource_raw['Resource_Type']);
        $location = collect($resource_raw['Location']);
        $thisresource_regions = [];
        $thisresource_skills = [];


        if (Arr::has($resource_raw, 'Resource_Region') && collect($resource_raw['Resource_Region'])->count()) {
            if (collect($resource_raw['Resource_Region'])->has('region_id')) {
//                $resource_regions = [collect($resource_raw['Resource_Region'])];
                $regions = [collect($resource_raw['Resource_Region'])];

            } else {
//                $resource_regions = collect($resource_raw['Resource_Region']);
                $regions = collect($resource_raw['Resource_Region']);

            }

            $regions_list = Arr::keyBy($resource_raw['Region'], 'id');

            foreach ($regions as $region) {

                $thisresource_regions[] = Arr::has($regions_list[$region['region_id']], 'description') ? $regions_list[$region['region_id']]['description'] : $region['region_id'];

            }

        }

        if ($location->count()) {
            if ($location->has('id')) {
                $resource_locations = [$location];
            } else {
                $resource_locations = $location;
            }

            $resource_locations = collect($resource_locations)->groupBy('id');
            $newresource_locations = $resource_locations->map(function ($location) {
                $latlong = collect($location)->first()['latitude'] . ',' . collect($location)->first()['longitude'];
                $response = GoogleMaps::load('geocoding')
                    ->setParam(['latlng' => $latlong])
                    ->get();
                $country = json_decode($response, false, 512, JSON_THROW_ON_ERROR)->results[0]->address_components[5]->short_name;
                return collect(collect($location)->first())->put('country', $country);
            })->values()->groupBy('id')
                ->map(function ($location) {
                    $latlong = collect($location)->first()['latitude'] . ',' . collect($location)->first()['longitude'];
                    $response = GoogleMaps::load('geocoding')
                        ->setParam(['latlng' => $latlong])
                        ->get();
                    $formatted_address = json_decode($response, false, 512, JSON_THROW_ON_ERROR)->results[0]->formatted_address;
                    return collect(collect($location)->first())->put('formatted_address', $formatted_address);
                });

        }


        if (Arr::has($resource_raw, 'Resource_Skill') && (collect($resource_raw['Resource_Skill'])->count()) && Arr::has($resource_raw, 'Skill')) {
            if (collect($resource_raw['Resource_Skill'])->has('skill_id')) {

                $skills = [collect($resource_raw['Skill'])->groupBy('id')];
            } else {

                $skills = collect($resource_raw['Skill'])->groupBy('id');
            }
            // group these so we can grab the proficiency by ID
            $resource_skills = collect($resource_raw['Resource_Skill'])->groupBy('skill_id');


            foreach ($resource_skills as $resource_skill) {

                $collected_skill = $skills[$resource_skill[0]['skill_id']][0];
                $thisresource_skills[] = [
                    $collected_skill['id'] =>
                        [
                            'readable' => $collected_skill['description'] . ' (' . $collected_skill['id'] . ')' . Arr::has($collected_skill, 'proficiency') ?: ' @ ' . $collected_skill['id']['proficiency'],
                            'value' => [
                                'id' => $collected_skill['id'],
                                'description' => $collected_skill['description'],
                                'proficiency' => Arr::has($collected_skill, 'proficiency') ? ' @ ' . $collected_skill['id']['proficiency'] : 1
                            ]
                        ]
                ];
            }
        }

        $start_location = [
            'id' => $newresource_locations[$resource['location_id_start']]['id'],
            'lat' => $newresource_locations[$resource['location_id_start']]['latitude'],
            'long' => $newresource_locations[$resource['location_id_start']]['longitude'],
            'formatted_from_google' => $newresource_locations[$resource['location_id_start']]['formatted_address'],
        ];

        $address_attributes = [
            'address_line1',
            'address_line2',
            'address_line3',
            'address_line4',
            'address_line5',
            'address_line6',
            'city',
            'description',
            'locality',
            'name'
        ];

        foreach ($address_attributes as $aa) {
            if (Arr::has($newresource_locations[$resource['location_id_start']], $aa)) {
                Arr::add($newresource_locations[$resource['location_id_start']], $aa, $newresource_locations[$resource['location_id_start']][$aa]);
            }
        }

        if (Arr::has($newresource_locations[$resource['location_id_start']], 'country')) {
            $region_type = $newresource_locations[$resource['location_id_start']]['country'] === 'US' ? 'state' : 'province';
            $ziptype = $newresource_locations[$resource['location_id_start']]['country'] === 'US' ? 'zip' : 'post_code';
            Arr::add($newresource_locations[$resource['location_id_start']], 'region_type', $region_type);
            Arr::add($newresource_locations[$resource['location_id_start']], 'zip_type', $ziptype);

            if (Arr::has($newresource_locations[$resource['location_id_start']], 'state')) {
                $start_location = Arr::add($newresource_locations[$resource['location_id_start']], $region_type, $newresource_locations[$resource['location_id_start']]['state']);
            }
            if (Arr::has($newresource_locations[$resource['location_id_start']], 'post_code_zip')) {
                $start_location = Arr::add($newresource_locations[$resource['location_id_start']], $ziptype, $newresource_locations[$resource['location_id_start']]['post_code_zip']);
            }
        }

        $resource_location = [
            'start_location' => [$start_location],
            'end_location' => $resource['location_id_start'] === $resource['location_id_end'] ? [$start_location] :
                [
                    // todo repeat above validation with end_location
                    'id' => $newresource_locations[$resource['location_id_end']]['id'],
                    'address' => $newresource_locations[$resource['location_id_end']]['address_line1'],
                    'city' => $newresource_locations[$resource['location_id_end']]['city'],
                    $newresource_locations[$resource['location_id_end']]['country'] === 'US' ? 'state' : 'province' => $newresource_locations[$resource['location_id_end']]['state'],
                    $newresource_locations[$resource['location_id_end']]['country'] ==='US' ? 'zip' : 'post_code' => $newresource_locations[$resource['location_id_end']]['post_code_zip'],
                    'lat' => $newresource_locations[$resource['location_id_end']]['latitude'],
                    'long' => $newresource_locations[$resource['location_id_end']]['longitude'],
                    'formatted_from_google' => $newresource_locations[$resource['location_id_end']]['formatted_address'],
                ]
        ];


        $resource_location = Arr::add($resource_location, 'same_start_and_end_location', $resource['location_id_start'] === $resource['location_id_end']);


        $formatted_resource = [
            'full_name' => $resource->get('first_name') . ' ' . $resource->get('surname'),
            'first_name' => $resource->get('first_name'),
            'surname' => $resource->get('surname'),
            'resource_id' => $resource->get('id'),
            'resource_type' => [
                'type_id' => $resource_type->get('id'),
                'description' => $resource_type->get('description'),
            ],
            'note' => $resource->get('memo'),
            'max_travel' =>
                [
                    'readable' => $resource->get('max_travel') ? CarbonInterval::fromString($resource->get('max_travel'))->forHumans(['options' => CarbonInterface::FLOOR]) : CarbonInterval::fromString($resource_type->get('max_travel'))->forHumans(['options' => CarbonInterface::FLOOR]),
                    'value' => $resource->get('max_travel') ?: $resource_type->get('max_travel'),
                    'source' => $resource->get('max_travel') ? 'resource' : 'inherited from resource_type'
                ],
            'max_travel_outside_shift_to_first_activity' =>
                [
                    'readable' => $resource->get('travel_to') ? CarbonInterval::fromString($resource->get('travel_to'))->forHumans(['options' => CarbonInterface::FLOOR]) : CarbonInterval::fromString($resource_type->get('travel_to'))->forHumans(['options' => CarbonInterface::FLOOR]),
                    'value' => $resource->get('travel_to') ?: $resource_type->get('travel_to'),
                    'source' => $resource->get('travel_to') ? 'resource' : 'inherited from resource_type'
                ],
            'max_travel_outside_shift_to_home' =>
                [
                    'readable' => $resource->get('travel_from') ? CarbonInterval::fromString($resource->get('travel_from'))->forHumans(['options' => CarbonInterface::FLOOR]) : CarbonInterval::fromString($resource_type->get('travel_from'))->forHumans(['options' => CarbonInterface::FLOOR]),
                    'value' => $resource->get('travel_from') ?: $resource_type->get('travel_from'),
                    'source' => $resource->get('travel_from') ? 'resource' : 'inherited from resource_type'
                ],
            'locations' => $resource_location,
        ];

        if (count($thisresource_regions)) {
            $formatted_resource = Arr::add($formatted_resource, 'regions', $thisresource_regions);
        }
        if (count($thisresource_skills)) {
            $formatted_resource = Arr::add($formatted_resource, 'skills', $thisresource_skills);
        }

        if (count($this->getResourceEvents())) {
            $formatted_resource = Arr::add($formatted_resource, 'events', $this->getResourceEvents());
        }
        $formatted_resource = Arr::add($formatted_resource, 'shifts', $this->getResourceShiftsFormatted());
        if (config('pso-services.settings.enable_debug')) {
            $formatted_resource = Arr::add($formatted_resource, 'raw', $resource_raw);
        }

//        return $this->pso_resource['Plan_Route'];
//        return $this->getResourceShiftsFormatted();
//        return $this->IFSPSOAssistService->apiResponse(200, 'Formatted Resource Returned', $formatted_resource);


//        $payload = [
//            'resource' => [
//                'raw' => $this->getResource($resource_id, $dataset_id, $base_url),
//                'utilization' => $this->getResourceUtilization(),
//                'events' => $this->getResourceEvents(),
//                'locations' => $this->getResourceLocations(),
//                'shifts' => $this->getResourceShiftsFormatted(),
//            ]
//        ];

        return response($formatted_resource, 200)
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
        $routes = collect($this->pso_resource['Plan_Route'])->groupBy('shift_id');

        return collect($this->shifts)->map(function ($item) use ($routes) {
            // this is a really nicely done mapping
            $shiftdate = Carbon::createFromDate($item['start_datetime'])->toFormattedDateString();
            $starttime = Carbon::createFromDate($item['start_datetime'])->format('H:i');
            $endtime = Carbon::createFromDate($item['end_datetime'])->format('H:i');
            $times = $starttime . ' - ' . $endtime;
            $difference = Carbon::createFromDate($item['start_datetime'])->diffInHours(Carbon::createFromDate($item['end_datetime']));
            $avg_travel = CarbonInterval::fromString($routes[$item['id']][0]['average_travel_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $total_travel_time = CarbonInterval::fromString($routes[$item['id']][0]['total_travel_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $total_on_site_time = CarbonInterval::fromString($routes[$item['id']][0]['total_on_site_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $total_break_time = CarbonInterval::fromString($routes[$item['id']][0]['total_break_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $total_private_time = CarbonInterval::fromString($routes[$item['id']][0]['total_private_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $total_unutilised_time = CarbonInterval::fromString($routes[$item['id']][0]['total_unutilised_time'])->forHumans(['options' => CarbonInterface::FLOOR]);
            $util_percent = $routes[$item['id']][0]['utilisation'];
            $total_allocations = $routes[$item['id']][0]['total_allocations'];
            $route_margin = $routes[$item['id']][0]['route_margin'];
            $overtime_period = Arr::has($item, 'overtime_period') ?
                CarbonInterval::fromString($item['overtime_period'])->forHumans(['options' => CarbonInterface::FLOOR]) : "no overtime";

            $shifts = collect($item)
                ->put('shift_date', $shiftdate)
                ->put('shift_span', $times)
                ->put('shift_duration', $difference)
                ->put('overtime_period', $overtime_period)
                ->put('utilization', [
                    'percent' => $util_percent,
                    'total_unutilised_time' => $total_unutilised_time,
                    'total_private_time' => $total_private_time,
                    'total_break_time' => $total_break_time,
                    'total_on_site_time' => $total_on_site_time,
                    'total_travel_time' => $total_travel_time,
                    'average_travel_time' => $avg_travel,
                    'total_allocations' => $total_allocations,
                    'route_margin' => $route_margin,
                ]);


            $shifts->pull('start_datetime');
            $shifts->pull('end_datetime');
            $shifts->pull('actual');
            $shifts->pull('split_allowed');
            $shifts->pull('resource_id');

            if (!isset($item['manual_scheduling_only'])) {
                $shifts->put('manual_scheduling_only', false);
            } else {

                $shifts->pull('manual_scheduling_only');

                $shifts->put('manual_scheduling_only', true); // this may need to be renamed to 'checked' if used in Vue.js
            }

            // todo figure out how to sort
            $shifts = $shifts->sortBy('shift_date');

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

                $this->utilization['dates'] = collect($this->pso_resource['Plan_Route'])->map(fn($item) => ['date' => Carbon::create($item['shift_start_datetime'])->toFormattedDateString()]);
                $this->utilization['utilization'] = collect($this->pso_resource['Plan_Route'])->map(fn($item) => ['utilization' => $item['utilisation']]);
                $this->utilization['travel'] = collect($this->pso_resource['Plan_Route'])->map(fn($item) => ['travel' => CarbonInterval::make(new DateInterval($item['average_travel_time']))->i]);
            }
        }

        return $this->utilization;
    }

    public function getScheduleableResources($request)
    {

        $schedule = new IFSPSOScheduleService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$schedule->getScheduleAsCollection($request->dataset_id, $request->base_url)) {

            return $this->IFSPSOAssistService->apiResponse(404, 'Dataset Does Not Exist', ["request_resources_from_dataset" => $request->dataset_id]);

        }
        $overall_schedule = $schedule->getScheduleAsCollection($request->dataset_id, $request->base_url)->collect();
        if (!$overall_schedule->has('Resources')) {
            return $this->IFSPSOAssistService->apiResponse(404, 'No resources exist in this schedule', $request);
        }

        $resources = collect($overall_schedule->get('Resources'));
        $shifts = collect($overall_schedule->get('Plan_Route'))->groupBy('resource_id');
        $events = collect($overall_schedule->get('Schedule_Event'));

        if (!Arr::has($events, 'id')) {
            $events = $events->mapToGroups(fn($item) => [
                $item['resource_id'] => [
                    'id' => $item['id'],
                    'event_type_id' => $item['event_type_id'],
                    'date_time_stamp' => $item['date_time_stamp'],
                    'event_date_time' => $item['event_date_time'],
                ]]);
        }


        $plans = collect($overall_schedule->get('Plan_Resource'))->keyBy('resource_id');
        $resources = $resources->map(function ($item) {
            return collect($item)
                ->put('fullname', $item['first_name'] . ' ' . $item['surname']);
        });

        $output = $resources->map(function ($item) use ($events) {
            // how do we do this if it's only one event ?
            if (isset($events[$item['id']])) {
                return collect($item)->put('events', $events[$item['id']]);
            } else {
                return $item;
            }
        })->
        map(function ($item) use ($plans, $shifts) {
            return collect($item)
                ->put('route', $plans[$item['id']])
                ->put('shift_count', count($shifts[$item['id']]))
                ->put('shift_max', collect($shifts[$item['id']])->max('shift_start_datetime'))
                ->put('shift_min', collect($shifts[$item['id']])->min('shift_start_datetime'));
        });

        return $this->IFSPSOAssistService->apiResponse(200, 'Resources Returned', $output);

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
                'resource_id' => (string)$resource_id
            ];
    }

    public function updateShift(Request $shift_data, $resource_id)
    {

        if (!$this->ResourceExists()) {
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Resource does not exist', ['shift_id' => $shift_data->shift_id, 'resource_id' => $resource_id], 'submitted_data');
        }

        $shift_set = $this->getResourceShiftsRaw();

        if (collect(collect($shift_set)->firstWhere('id', $shift_data->shift_id))->isEmpty()) {
            // don't bother doing anything if the shift doesn't exist
            return $this->IFSPSOAssistService->apiResponse(404, 'Specified Shift ID does not exist', ['shift_id' => $shift_data->shift_id, 'resource_id' => $resource_id], 'submitted_data');
        }

        // build the json for the RAM_Rota_Item
        // the first param is looking at the list of shifts and finding the details on the one we're modifying

        $rawshift = collect(collect($shift_set)->firstWhere('id', $shift_data->shift_id));
        $raw_shift_data['turn_manual_scheduling_on'] = $rawshift->get('manual_scheduling_only') ?: false;
        $raw_shift_data['shift_type'] = $shift_data->shift_type ?: $rawshift->get('shift_type_id');
        $raw_shift_data['start_datetime'] = $shift_data->start_datetime ?: $rawshift->get('start_datetime');
        $raw_shift_data['end_datetime'] = $shift_data->end_datetime ?: $rawshift->get('end_datetime');
        if (Carbon::make($raw_shift_data['end_datetime'])->lt(Carbon::make($raw_shift_data['start_datetime']))) {
            return $this->IFSPSOAssistService->apiResponse(500, 'Start Date cannot be greater than End Date', ['start_datetime' => $raw_shift_data['start_datetime'], 'end_datetime' => $raw_shift_data['end_datetime']], 'submitted_data');
        }
        $description = "Shift updated via " . $this->service_name . ". (" . Carbon::now()->toDateTimeString() . ")";
        if ($shift_data->has('turn_manual_scheduling_on')) {
            $raw_shift_data['turn_manual_scheduling_on'] = $shift_data->turn_manual_scheduling_on;
            $description = "Manual Scheduling Only set to " . ($shift_data->turn_manual_scheduling_on ? "ON" : "OFF") . " via " . $this->service_name . ". (" . Carbon::now()->toDateTimeString() . ")";
        }

        //defaulting rota_id to dataset_id if rota_id is null
        $rota_id = PSOHelper::RotaID($shift_data->dataset_id, $shift_data->rota_id);
        $ram_rota_item_payload = $this->RAMRotaItemPayload(
            $rawshift,
            $rota_id,
            $raw_shift_data,
            $description
        );

        $ram_update_payload = $this->RAMUpdatePayload($shift_data->dataset_id, $description);

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
                $rota_id
            );

            // get the resource again
            $resource_init = new self($shift_data->base_url, $this->token, null, null, null, true);
            $resource_init->getResource($resource_id, $shift_data->dataset_id, $shift_data->base_url);
            $fresh_shifts = $resource_init->getResourceShiftsRaw();

            $shift_in_question = collect(collect($fresh_shifts)->firstWhere('id', $shift_data->shift_id));

            // compare the shift
            if ($description === $shift_in_question['description']) {
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
            $rota_id
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

    private function RAMResourceUpdatePayload($ram_update_payload, $resource_payload, $location_payload, $skills_payload, $division_payload)
    {
        $json =
            [
                '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                'RAM_Update' => $ram_update_payload,
                'RAM_Resource' => $resource_payload,
                'RAM_Location' => $location_payload
            ];


        if (count($skills_payload) > 0) {
            $json = Arr::add($json, 'RAM_Resource_Skill', $skills_payload);
        }
        if (count($division_payload) > 0) {
            $json = Arr::add($json, 'RAM_Resource_Division', $division_payload);

        }

        return ['DsModelling' => $json];
    }

    private function RAMUpdatePayload($dataset_id, $description): array
    {
        return [
            'organisation_id' => '2',
            'dataset_id' => $dataset_id,
            'user_id' => $this->service_name . ' user',
            'ram_update_type_id' => 'CHANGE',
            'is_master_data' => true,
            'description' => $description
        ];
    }


    private function RAMRotaItemPayload($rawshift, $rota_id, $shift_data, $description)
    {


        $payload = [
            'id' => $rawshift->get('id'),
            'ram_rota_id' => (string)$rota_id,
            'manual_scheduling_only' => $shift_data['turn_manual_scheduling_on'],
            'ram_resource_id' => $rawshift->get('resource_id'),
            'start_datetime' => $shift_data['start_datetime'],
            'end_datetime' => $shift_data['end_datetime'],
            'ram_shift_category_id' => (string)$shift_data['shift_type'],
            'description' => (string)$description
        ];

        if ($rawshift->get('overtime_period')) {
            $payload = Arr::add($payload, 'overtime_period', $rawshift->get('overtime_period'));
        }

        return $payload;
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
        $duration = PSOHelper::setPSODuration($request->duration);

        $tz = PSOHelper::setTimeZone($request->time_zone);

        $base_time = $request->base_time . ':00' . $tz;

        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, 'Create Unavailability via ' . $this->service_name);
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
            $grouped_activities = collect($schedule->collect()->first()['Activity'])->mapWithKeys(fn($activity) => [$activity['id'] => $activity])->only($unavailabilities);


            $grouped_allocations = collect($schedule->collect()->first()['Allocation'])->mapWithKeys(fn($allocation) => [$allocation['activity_id'] => $allocation])->only($unavailabilities);

            if ($grouped_activities->count() === 0 || $grouped_allocations->count() === 0) {
                // if none of those exist in the schedule return a 404
                return $this->IFSPSOAssistService->apiResponse(404, 'no NAs found', ['NAs sent' => $unavailabilities]);
            }
        } else {
            return $this->IFSPSOAssistService->apiResponse(404, 'Schedule is Pretty Empty', ['NAs sent' => $unavailabilities]);
        }

        // all these NAs will share a single time pattern based on the input (if there is one)
        $time_pattern_id = Str::uuid()->getHex();

        $duration = $request->duration ? PSOHelper::setPSODuration($request->duration) : $grouped_allocations->first()['duration'];

        $tz = PSOHelper::setTimeZone($request->time_zone, true, $grouped_allocations);

        $category_id = $request->category_id ?: $grouped_activities->first()['activity_type_id'];
        $description = ($request->description ?: $grouped_activities->first()['description']) . ' - Updated via ' . $this->service_name . ' on ' . Carbon::now()->toDayDateTimeString();

        $base_time = ($request->base_time ? $request->base_time . ':00' : Str::of($grouped_allocations->first()['activity_start'])->substr(1, 19)) . $tz;
        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, ($grouped_activities->count() > 0 ? 'Mass ' : '') . 'Update Unavailability via ' . $this->service_name);
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
            'ram_unavailability_category_id' => (string)$category_id,
            'description' => (string)$description
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
        $ram_update_payload = $this->RAMUpdatePayload($request->dataset_id, 'Deleted Unavailability via ' . $this->service_name);

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

    private function ResourceExists($collection = null)
    {
        // todo fix reference to collection
        $collection = $collection ?: $this->pso_resource;
        if (!Arr::has($collection, 'Resources')) {
            return false;
        }
        return true;
    }

    public function createResource(Request $request)
    {

        // check if the lat/longs are valid
        foreach ($request->lat as $lat) {
            if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
                return $this->IFSPSOAssistService->apiResponse(406, 'Invalid Latitude found in Data', ['invalid_latitude' => $lat], 'submitted_data');
            }
        }

        foreach ($request->long as $long) {
            if (!is_numeric($long) || $long < -180 || $long > 180) {
                return $this->IFSPSOAssistService->apiResponse(406, 'Invalid Longitude found in Data', ['invalid_longitude' => $long], 'submitted_data');
            }
        }

        $counts = [
            "resources_requested" => $request->resources_to_create ?? 1,
            "lats" => count($request->lat),
            "longs" => count($request->long)
        ];

        if ($request->ids) {
            $counts = Arr::add($counts, 'ids', count($request->ids));
        }

        if ($request->names) {
            $counts = Arr::add($counts, 'names', count($request->names));
        }


        $count_to_use = min($counts);


        $values_are_equal = count(array_unique(Arr::flatten($counts), SORT_REGULAR));

        $input_used = [
            "min_value" => $count_to_use,
            "taken_from" => $values_are_equal === 1 ? "all values equal, good job" : array_search(min($counts), $counts)
        ];
// refactored above
//        if (count(array_unique(Arr::flatten($counts), SORT_REGULAR)) === 1) {
//            $input_used = [
//                "min_value" => $count_to_use,
//                "taken_from" => "all values equal, good job"
//            ];
//
//        } else {
//            $input_used = [
//                "min_value" => $count_to_use,
//                "taken_from" => array_search(min($counts), $counts)
//            ];
//        }

        $faker = Factory::create();


        $skills = $regions = $resources = $locations = [];

        for ($n = 0; $n <= $count_to_use - 1; $n++) {
            // create the resource object
            $splitname = null;
            if ($request->names) {
                $splitname = explode(' ', $request->names[$n], 2);
            }
            $resource_request = new Collection([
                'first_name' => $splitname ? $splitname[0] : $faker->firstName(),
                'surname' => $splitname ? (!empty($splitname[1]) ? $splitname[1] : '') : $faker->lastName(),
                'resource_type_id' => $request->resource_type_id,
                'skill' => $request->skill,
                'region' => $request->region
            ]);
            if ($request->ids) {
                $resource_request->put('resource_id', $request->ids[$n]);
            }


            $resource = new PSOResource(json_decode($resource_request->toJson(), false, 512, JSON_THROW_ON_ERROR), $request->lat[$n], $request->long[$n]);
            $resources[] = $resource->ResourceToJson();
            $locations[] = $resource->ResourceLocation();
            if ($resource->ResourceSkills()) {
                $skills[] = $resource->ResourceSkills();
            }
            if ($resource->ResourceRegion()) {
                $regions[] = $resource->ResourceRegion();
            }
        }

        $desc = 'Add ' . $input_used['min_value'] . ' resources.' . ($values_are_equal === 1 ? 'yes' : ' Limited by ' . $input_used['taken_from']);


        $ram_update_payload = $this->RAMUpdatePayload($request->modelling_dataset_id, $desc);

        $full_payload = $this->RAMResourceUpdatePayload($ram_update_payload, $resources, $locations, $skills, $regions);

        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso,
            $full_payload,
            $this->token,
            $request->base_url,
            $desc,
            true,
            $request->modelling_dataset_id,
            $request->rota_id
        );

    }

}
