<?php

namespace App\Http\Controllers;


use App\Services\IFSPSOAssistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use App\Services\IFSPSOScheduleService;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class PSOSandboxController extends Controller
{

    public function test()
    {

//        $schedule = IFSPSOScheduleService::getSchedule($base_url, $dataset_id, $this->token);
        $schedule = new IFSPSOAssistService(config('pso-services.debug.base_url'),
            null,
//            config('pso-services.debug.username'),
            'admin',
            config('pso-services.debug.password'),
            "Default", true);

        $theschedule = $schedule->getSchedule(config('pso-services.debug.base_url'), 'NORTH');
//        return $theschedule->collect()->first()['Activity'];
        $fullschedule = $theschedule->collect()->first();
        $activity = collect($fullschedule['Activity']);


        // get Activity;
        if ($activity->count()) {
            if ($activity->has('id')) {
                $activities = [$activity];
            } else {
                $activities = $activity;
            }

        }
        $activity_keys = collect($activities)->pluck('id');
        $activity_locations = collect($activities)->pluck('location_id');
        $required_statuses = [];
        $required_skills = [];
        $required_locations = [];
        $required_location_regions = [];

        // then get Activity_Skill, Activity_Status,  where activity_id in list of activities
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
            $required_locations = ['Location' => $locations->whereIn('id', $activity_locations)->values()];
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

        return $required_init_data = [
            'Activities' => $activities,
            'Activity_Status' => $required_statuses,
            'Activity_Skill' => $required_skills,
            'Location' => $required_locations,
            'Location_Region' => $required_location_regions
        ];


    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //

        $IFSPSOAssistService = new IFSPSOAssistService(
            config('pso-services.debug.base_url'),
            null,
//            config('pso-services.debug.username'),
            'admin',
            config('pso-services.debug.password'),
            "Default", true);

        return $IFSPSOAssistService->token;
        /*
                $usage = Http::withHeaders([
                    'apiKey' => $this->IFSPSOAssistService->token
                ])->get(
                    config('pso-services.debug.base_url') . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/usage',
                    [
                        'minimumDateTime' => '2022-11-01',
                        'maximumDateTime' => '2022-11-02'
                    ]);

                $data = collect($usage->collect()->first());
        //        return $data;

                $mystuff = collect($usage->collect()->first())->map(function ($item, $key) {

                    $type = match ($item['ScheduleDataUsageType']) {
                        0 => 'Resource_Count',
                        1 => 'Activity_Count',
                        2 => 'DSE_Window',
                        3 => 'ABE_Window',
                        4 => 'Dataset_Count',
                    };

                    return collect($item)->put('count_type', $type);
                })->mapToGroups(function ($item, $key) {

                    return [$item['DatasetId'] => $item];
                });


                foreach ($mystuff as $dataset => $value) {
                    $newdata[$dataset] = collect($value)->mapToGroups(function ($item, $key) {
                        return [$item['count_type'] => $item];

                    });
                }
                return $newdata;

        //        $newdata = $data->where('ScheduleDataUsageType', 0)->mapToGroups(function ($item, $key) {
        //            return [$item['DatasetId'] => $item];
        //        });

                return $mystuff;

                $pso_schedule = Http::withHeaders([
                    'apiKey' => $this->token
                ])->get(
                    'https://' . $base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
        //            'https://' . 'webhook.site/b54231dc-f3c4-42de-af86-11db17198493' . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    [
                        'includeInput' => 'true',
                        'includeOutput' => 'true',
                        'datasetId' => $dataset_id
                    ]);

                return collect($pso_schedule->collect()->first());

                $test = new IFSPSOAssistService('doobas');
                return $test;

                /*
                        $schedule = new IFSPSOScheduleService('cb847e5e-8747-4a02-9322-76530ef38a19');
                        return $schedule->getResDetails();
                //        $resources =collect($schedule->getSchedule('W&C Prod')->collect()->first())->get('Resources');
                        $resources =collect($schedule->getSchedule('W&C Prod')->collect()->first());
                        return $resources;
                        foreach ($resources as $resource) {
                            echo $resource['first_name'];
                        }
                */

        $dataset_id = "W&C Prod";
        $resource = new IFSPSOResourceService('cb847e5e-8747-4a02-9322-76530ef38a19');
        $test = $resource->getResource('21307', 'W&C Prod');
//        return $test;
        $shifts = $resource->getResourceShiftsRaw();

        return $resource->setEvent('x', 'RO', '21307', $dataset_id);
        return $shifts;


        return $resource->setManualScheduling('blahblah', 'c9e4c906937b4b2199fcf7b40c1038dc', $shifts, $dataset_id, $dataset_id, 'Manual Scheduling Shift', true);

//        return $this->resource;


        return $shifts;


        if (isset($this->resource['Plan_Route'])) {
            if (isset($this->resource['Plan_Route']['plan_id'])) {
                $mystuff['dates'][] = ['date' => $this->resource['Plan_Route']['shift_start_datetime']];
                $mystuff['utilization'][] = ['utilization' => $this->resource['Plan_Route']['utilisation']];
                $mystuff['travel'][] = ['travel' => $this->resource['Plan_Route']['average_travel_time']];

            } else {
                $mystuff['dates'] = collect($this->resource['Plan_Route'])->map(function ($item, $key) {
                    return ['date' => $item['shift_start_datetime']];
                });
                $mystuff['utilization'] = collect($this->resource['Plan_Route'])->map(function ($item, $key) {
                    return ['utilization' => $item['utilisation']];
                });
                $mystuff['travel'] = collect($this->resource['Plan_Route'])->map(function ($item, $key) {
                    return ['travel' => CarbonInterval::make(new \DateInterval($item['average_travel_time']))->i];
                });
            }
        }

//        return $mystuff;

        if (isset($this->resource['Schedule_Event'])) {
            if (isset($this->resource['Schedule_Event']['id'])) {
                $events[] = $this->resource['Schedule_Event'];
            } else {
                $events = $this->resource['Schedule_Event'];
            }
        }


        if (isset($this->resource['Shift'])) {
            if (isset($this->resource['Shift']['id'])) {
                $shifts[] = $this->resource['Shift'];
            } else {
                $shifts = $this->resource['Shift'];
            }
        }

        $newshifts = collect($shifts)->map(function ($item, $key) {
            $shiftdate = Carbon::createFromDate($item['start_datetime'])->toDateString();
            $starttime = Carbon::createFromDate($item['start_datetime'])->toTimeString();
            $endtime = Carbon::createFromDate($item['end_datetime'])->toTimeString();
            $times = $starttime . ' - ' . $endtime;
            $difference = Carbon::createFromDate($item['start_datetime'])->diffInHours(Carbon::createFromDate($item['end_datetime']));


            $shifts = collect($item)
                ->put('shift_date', $shiftdate)
                ->put('shift_times', $times)
                ->put('shift_duration', $difference);

            if (!isset($item['manual_scheduling_only'])) {
                $shifts->put('manual_scheduling_only', false);
            }

            $shifts->pull('start_datetime');
            $shifts->pull('end_datetime');
            $shifts->pull('actual');
            $shifts->pull('split_allowed');
            $shifts->pull('resource_id');

            return $shifts;
        });

        $newneshifts = $newshifts->map(function ($item, $key) {
            $stuff = collect($item);
            $stuff->pull('start_datetime');
            $stuff->pull('end_datetime');
            $stuff->pull('actual');
            $stuff->pull('split_allowed');
            $stuff->pull('resource_id');
            return $stuff->all();

        });
//        return $newneshifts;
        return $newshifts;


//        return $events;

//        return $mystuff['travel'];

        return $this->resource;
        $sched = new IFSPSOScheduleService('cb847e5e-8747-4a02-9322-76530ef38a19');
        return $sched->getScheduleAsCollection('W&C Prod');

        $res = new IFSPSOResourceService('cb847e5e-8747-4a02-9322-76530ef38a19');
        return $res->getScheduleableResources('W&C Prod');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function store(Request $request)
    {
        //
        Log::info('received request from 1111PSO');

        return response()->json([
            'status' => 418,
            'description' => 'here is a 418',
        ], 418, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);


        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        return $resource_init->getResourceForWebApp($request->resource_id, $request->dataset_id, $request->base_url);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show()
    {
        //
//        return
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
