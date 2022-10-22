<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use App\Services\IFSPSOScheduleService;
use App\Services\IFSPSOGarabageService;
use Illuminate\Support\Str;


class PSOScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

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
        $resource = new IFSPSOGarabageService('cb847e5e-8747-4a02-9322-76530ef38a19');
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
        return $sched->getSchedule('W&C Prod');

        $res = new IFSPSOGarabageService('cb847e5e-8747-4a02-9322-76530ef38a19');
        return $res->getScheduleableResources('W&C Prod');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public
    function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function show()
    {
        //
//        return
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function destroy($id)
    {
        //
    }
}
