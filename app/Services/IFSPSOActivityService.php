<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class IFSPSOActivityService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;
    private $activity_object;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }


    public function getActivity(Request $request, $activity_id)
    {
        $activity = Http::withHeaders(['apiKey' => $this->token])
            ->get($request->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/activity',
                [
                    'includeOutput' => true,
                    'datasetId' => $request->dataset_id,
                    'activityId' => $activity_id,
                    'tableFilter' => 'Activity, SLA_Type, Activity_SLA, Location, Activity_Status'
                ]
            );
        return $this->activity_object = $activity->collect();
    }

    public function activityExists()
    {
        return collect($this->activity_object->first())->has('Activity');
    }


    public function sendCommitActivity($pso_sds_broadcast, $debug_mode = false): JsonResponse
    {

        // should be assumed that environment and auth for this service is pre-configured

        $dataset_id = config('pso-services.debug.dataset_id');
        $base_url = config('pso-services.debug.base_url');
        $activity_part_payload = [];

        // this is the whole broadcast
        // chunk out just the suggested dispatch

        $suggestions = collect($pso_sds_broadcast)->get('Suggested_Dispatch');

        // this is to check if the suggestions is an array of objects or an object
        if (isset($suggestions->plan_id)) $newsuggestions[] = $suggestions; else {
            $newsuggestions = $suggestions;
        }

        // build the individual status update
        foreach ($newsuggestions as $suggestion) {
            $activity_part_payload[] = $this->ActivityStatusPartPayload($suggestion['activity_id'], config('pso-services.statuses.commit_status'), $suggestion['resource_id'], $suggestion['expected_start_datetime'], 'From the Commit Service Thingy');
        }

        // build the full payload
        $activity_status_payload = $this->ActivityStatusFullPayload($dataset_id, $activity_part_payload, 'Committing ' . count($activity_part_payload) . (count($activity_part_payload) > 1 ? ' Activities' : ' activity') . ' based on the SDS');

        $activity_status = Http::withHeaders(['apiKey' => $this->token])
            ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                $activity_status_payload
            );

        // todo log that we received it, sent it and the response from the server

        if ($debug_mode) {
            $pso_resource = Http::patch('https://webhook.site/' . config('pso-services.debug.webhook_uuid'), $activity_status_payload);

            return response()->json([
                'status' => 200,
                'description' => 'Service has sent payload to PSO',
                'original_payload' => [$activity_status_payload]
            ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }

    }

    public function updateActivityStatus($request, $status): JsonResponse
    {

        // build the payload
        $pso_status = config('pso-services.statuses.all.' . $status);

        $activity_part_payload = $this->ActivityStatusPartPayload(
            $request->activity_id,
            $pso_status,
            $request->resource_id,
            $request->date_time_fixed,
            'From the change status thingy'
        );

        $payload = $this->ActivityStatusFullPayload($request->dataset_id, $activity_part_payload, 'Status Change from the thingy');


        if ($request->send_to_pso) {

            $response = $this->IFSPSOAssistService->sendPayloadToPSO($payload, $this->token, $request->base_url);

            if ($response->serverError()) {
                return $this->IFSPSOAssistService->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('InternalId') == "-1") {
                return $this->IFSPSOAssistService->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('InternalId') != "-1") {
                return $this->IFSPSOAssistService->apiResponse(200, "Payload sent to PSO", $payload);
            }

            if ($response->json('Code') == 401) {
                return $this->IFSPSOAssistService->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }

            if ($response->status() == 500) {
                return $this->IFSPSOAssistService->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
            }

            if ($response->status() == 401) {
                return $this->IFSPSOAssistService->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }
        } else {
            return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO", $payload);
        }

        return $this->IFSPSOAssistService->apiResponse(202, "Payload not sent to PSO", $payload);

    }


    private function ActivityStatusFullPayload($dataset_id, $activity_status_payload, $description): array
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData($description, $dataset_id, "CHANGE"),
                'Activity_Status' => $activity_status_payload,

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


    public function deleteActivity(Request $request, $description = null)
    {
        $delete_activity_payload = $this->DeleteActivityPayloadPart($request->activity_id);
        $payload = $this->DeleteObjectFull($delete_activity_payload, $request->dataset_id, 'Activity');

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $description);
//        return response()->json([
//            'status' => 202,
//            'description' => $description ?: 'not send to PSO',
//            'original_payload' => [$payload]
//        ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    public function deleteSLA(Request $request, $description = null): JsonResponse
    {
        // build the payload
        $delete_sla_payload = $this->DeleteSLAPayloadPart($request->activity_id, $request->sla_type_id, $request->priority, $request->start_based);
        // build the full payload
        $payload = $this->DeleteObjectFull($delete_sla_payload, $request->dataset_id, 'SLA');
//        $payload = $this->DeleteSLAPayloadFull($delete_sla_payload, $request->dataset_id); // refactored this to use to deleteobjectfull
        return $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, $description);

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


    private function DeleteObjectFull($payload, $dataset_id, $description): array
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData("Deleting " . $description . " from the thingy", $dataset_id, 'CHANGE'),
                'Object_Deletion' => $payload
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
