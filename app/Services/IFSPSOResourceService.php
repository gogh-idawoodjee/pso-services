<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


class IFSPSOResourceService extends IFSService
{

    private $pso_resource;
    private $schedulabl;

    public function getResource($resource_id, $dataset_id)
    {

        $this->pso_resource = Http::withHeaders(['apiKey' => $this->token])
            ->get('https://' . $this->pso_environment->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/resource?includeOutput=true&datasetId=' . urlencode($dataset_id) . '&resourceId=' . $resource_id);

        return $this->pso_resource;

    }

    public function getScheduleableResources($dataset_id)
    {
        $schedule = new IFSPSOScheduleService('cb847e5e-8747-4a02-9322-76530ef38a19');
        $overallschedule = collect($schedule->getSchedule($dataset_id)->collect()->first());
        $bigobject['resources'] = $overallschedule->get('Resources');
        $bigobject['events'] = $overallschedule->get('Schedule_Event');
        $bigobject['plans'] = $overallschedule->get('Plan_resource');
        $grouped = collect($bigobject['plans'])->mapToGroups(function ($item, $key) {
            return [$item['resource_id'] => [
                'resource_margin' => $item['resource_margin'],
                'total_allocatoins' => $item['total_allocations']
            ]];
        });
        return $grouped;
        return collect($schedule->getSchedule($dataset_id)->collect()->first())->get('Resources');
    }


}
