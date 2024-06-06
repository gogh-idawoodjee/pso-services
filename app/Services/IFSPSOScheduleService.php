<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;


class IFSPSOScheduleService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function getScheduleAsCollection($dataset_id, $base_url)
    {
        try {
            $pso_schedule = Http::withHeaders([
                'apiKey' => $this->token
            ])->timeout(5)
                ->connectTimeout(5)->get(
                    $base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
//            'https://' . 'webhook.site/b54231dc-f3c4-42de-af86-11db17198493' . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    [
                        'includeInput' => 'true',
                        'includeOutput' => 'true',
                        'datasetId' => $dataset_id
                    ]);
        } catch (ConnectionException) {
            return false;
        }

        return collect($pso_schedule->collect()->first());

    }

    public static function getSchedule($base_url, $dataset_id, $token, $include_input = 'true', $include_output = 'true')
    {
        try {
            $schedule = Http::withHeaders([
                'apiKey' => $token
            ])->timeout(5)
                ->connectTimeout(5)
                ->get(
                    $base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    [
                        'includeInput' => $include_input,
                        'includeOutput' => $include_output,
                        'datasetId' => $dataset_id
                    ]);

        } catch (ConnectionException) {
            return false;
        }

        return $schedule;

    }


}
