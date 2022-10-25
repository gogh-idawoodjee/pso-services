<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


class IFSPSOScheduleService extends IFSService
{

    private $pso_schedule;

    public function getSchedule($dataset_id, $base_url)
    {

        $this->pso_schedule = Http::withHeaders([
            'apiKey' => $this->token
        ])->get(
            'https://' . $base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
//            'https://' . 'webhook.site/b54231dc-f3c4-42de-af86-11db17198493' . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
            [
                'includeInput' => 'true',
                'includeOutput' => 'true',
                'datasetId' => $dataset_id
            ]);

        return collect($this->pso_schedule->collect()->first());

    }


}
