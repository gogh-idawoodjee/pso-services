<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class IFSPSOActivityService extends IFSService
{

    const COMMIT_STATUS = 30;
    private array $pso_statuses;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        // todo move this to a config file
        $this->pso_statuses = [
            'travelling' => 50,
            'ignore' => -1,
            'committed' => 30,
            'sent' => 32,
            'unallocated' => 0,
            'downloaded' => 35,
            'accepted' => 40,
            'waiting' => 55,
            'onsite' => 60,
            'pendingcompletion' => 65,
            'visitcomplete' => 68,
            'completed' => 70,
            'incomplete' => 80
        ];

    }

    public function getActivity($activity_id, $dataset_id): Collection
    {
        //todo find out who's using this function if at all

        $pso_activity = Http::withHeaders(['apiKey' => $this->token])
            ->get('https://' . $this->pso_environment->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/activity?includeOutput=true&datasetId=' . urlencode($dataset_id) . '&activityId=' . $activity_id);

        return collect($pso_activity);

    }

    public function sendCommitActivity($pso_sds_broadcast): JsonResponse
    {

        // should be assumed that environment and auth for this service is pre-configured
        // todo where are we going to get these values from?

        $dataset_id = 'W&C Prod';

        // this is the whole broadcast
        // chunk out just the suggested dispatch

        $suggestions = collect($pso_sds_broadcast)->get('Suggested_Dispatch');

        // this is to check if the suggestions is an array of objects or an object
        if (isset($suggestions->plan_id)) $newsuggestions[] = $suggestions; else {
            $newsuggestions = $suggestions;
        }

        // build the individual status update
        foreach ($newsuggestions as $suggestion) {
            $activity_part_payload[] = $this->ActivityStatusPartPayload($suggestion['activity_id'], self::COMMIT_STATUS, $suggestion['resource_id'], $suggestion['expected_start_datetime'], 'From the Commit Service Thingy');
        }

        // build the full payload
        $activity_status_payload = $this->ActivityStatusFullPayload($dataset_id, $activity_part_payload, 'Committing ' . count($activity_part_payload) . (count($activity_part_payload) > 1 ? ' Activities' : ' activity') . ' based on the SDS');

        $pso_resource = Http::patch('https://webhook.site/55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3', $activity_status_payload);

        return response()->json([
            'status' => 202,
            'description' => 'not send to PSO',
            'original_payload' => [$activity_status_payload]
        ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);

    }

    public function updateActivityStatus($request, $status): JsonResponse
    {

        // todo add this to a config file
        $statuses_requiring_resources = ['travelling', 'committed', 'sent', 'downloaded', 'accepted', 'waiting', 'onsite',
            'pendingcompletion', 'visitcomplete', 'completed', 'incomplete'];

        // build the payload
        $pso_status = $this->pso_statuses[$status];

        $activity_part_payload = $this->ActivityStatusPartPayload(
            $request->activity_id,
            $pso_status,
            $request->resource_id,
            $request->date_time_fixed, 'From the change status thingy');

        $activity_status_payload = $this->ActivityStatusFullPayload($request->dataset_id, $activity_part_payload, 'Status Change from the thingy');

        return response()->json([
            'status' => 202,
            'description' => 'not send to PSO',
            'original_payload' => [$activity_status_payload]
        ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);


    }


    private function ActivityStatusFullPayload($dataset_id, $activity_status_payload, $description): array
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData($description, $dataset_id, "CHANGE"),
                'Activity_Status' => $activity_status_payload,//$this->ActivityStatusPartPayload($activity_id, $status, $resource_id, $fixed_resource, $date_time_fixed),

            ]
        ];
    }

    private function ActivityStatusPartPayload($activity_id, $status, $resource_id, $date_time_fixed, $reason): array
    {
        $payload = [
            'activity_id' => "$activity_id",
            'status_id' => $status,
            'date_time_status' => Carbon::now()->toAtomString(),
            'date_time_stamp' => Carbon::now()->toAtomString(),
            'visit_id' => 1,
            'fixed' => $status != -1 && $status != 0,
            'reason' => $reason
        ];

        if ($status != -1 && $status != 0) {
            $payload = Arr::add($payload, 'resource_id', "$resource_id");
            $payload = Arr::add($payload, 'date_time_fixed', $date_time_fixed);
            $payload = Arr::add($payload, 'date_time_earliest', $date_time_fixed);
        }

        return $payload;

    }


    private function InputReferenceData($description, $dataset_id, $input_type): array
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

    public function deleteSLA(Request $request): JsonResponse
    {
        // build the payload
        $delete_sla_payload = $this->DeleteSLAPayloadPart($request->activity_id, $request->sla_type_id, $request->priority, $request->start_based);
        // build the full payload
        $payload = $this->DeleteSLAPayloadFull($delete_sla_payload, $request->dataset_id);

        return response()->json([
            'status' => 202,
            'description' => 'not send to PSO',
            'original_payload' => [$payload]
        ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    private function DeleteSLAPayloadFull($sla_payload, $dataset_id): array
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData("Deleting SLA from the thingy", $dataset_id, 'CHANGE'),
                'Object_Deletion' => $sla_payload,//$this->ActivityStatusPartPayload($activity_id, $status, $resource_id, $fixed_resource, $date_time_fixed),

            ]
        ];
    }

    private function DeleteSLAPayloadPart($activity_id, $sla_type, $priority, $start_based): array
    {
        return [
            'object_type_id' => 'Activity_SLA',
            'object_pk_name1' => 'activity_id',
            'object_pk_name2' => 'sla_type_id',
            'object_pk_name3' => 'priority',
            'object_pk_name4' => 'start_based',
            'object_pk1' => $activity_id,
            'object_pk2' => $sla_type,
            'object_pk3' => $priority ?: 1,
            'object_pk4' => (bool)$start_based,
        ];
    }
}
