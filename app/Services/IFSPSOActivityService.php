<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class IFSPSOActivityService extends IFSService
{

    private $pso_activity;
    const COMMIT_STATUS = 30;

    public function getActivity($activity_id, $dataset_id)
    {

        $this->pso_activity = Http::withHeaders(['apiKey' => $this->token])
            ->get('https://' . $this->pso_environment->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/activity?includeOutput=true&datasetId=' . urlencode($dataset_id) . '&activityId=' . $activity_id);

        return $this->pso_activity;

    }

    public function sendCommitActivity($pso_sds_broadcast)
    {

        // should be assumed that environment and auth for this service is pre-configured
        // where are we gonig to get these values from?
        $dataset_id = 'W&C Prod';

        // this is the whole broadcast
        // chunk out just the suggested dispatch

//        return $pso_sds_broadcast;
        $suggestions = collect($pso_sds_broadcast)->get('Suggested_Dispatch');

        if (isset($suggestions->plan_id)) {
            $newsuggestions[] = $suggestions;
        } else {
            $newsuggestions = $suggestions;
        }

        // build the individual status update
        foreach ($newsuggestions as $suggestion) {
            $activity_part_payload[] = $this->ActivityStatusPartPayload($suggestion['activity_id'], self::COMMIT_STATUS, $suggestion['resource_id'], $suggestion['expected_start_datetime']);
        }

        // build the full payload
        $activity_status_payload = $this->ActivityStatusFullPayload($dataset_id, $activity_part_payload, 'Committing ' . count($activity_part_payload) . (count($activity_part_payload) > 1 ? ' Activities' : ' activity') . ' based on the SDS');

        $pso_resource = Http::patch('https://webhook.site/55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3', $activity_status_payload);

        return response()->json([
            'status' => 202,
            'description' => 'not send to PSO',
            'original_payload' => [$activity_status_payload]
        ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);


        return $activity_status_payload;
//        $send_activity_status = Http::withHeaders(['apiKey' => $token])
//            ->post('https://' . $this->pso_environment->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
//           $activity_status_payload
//            );


    }


    private function ActivityStatusFullPayload($dataset_id, $activity_status_payload, $description)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData($description, $dataset_id, "CHANGE"),
                'Activity_Status' => $activity_status_payload,//$this->ActivityStatusPartPayload($activity_id, $status, $resource_id, $fixed_resource, $date_time_fixed),

            ]
        ];
    }

    private function ActivityStatusPartPayload($activity_id, $status, $resource_id, $date_time_fixed)
    {
        return
            [
                'activity_id' => "$activity_id",
                'status_id' => $status,
                'date_time_status' => Carbon::now()->toAtomString(),
                'date_time_stamp' => Carbon::now()->toAtomString(),
                'date_time_earliest' => $date_time_fixed,
                'visit_id' => 1,
                'fixed' => true,
                'resource_id' => "$resource_id",
                'date_time_fixed' => $date_time_fixed,
                'reason' => 'From the Commit Service Thingy'
            ];
    }


    private function InputReferenceData($description, $dataset_id, $input_type)
    {
        return
            [
                'datetime' => Carbon::now()->toAtomString(),
                'id' => Str::orderedUuid()->getHex()->toString(),
                'description' => "$description",
                'input_type' => strtoupper($input_type),
                'organisation_id' => '2',
                'dataset_id' => $dataset_id,
            ];

    }
}
