<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSOActivity;
use App\Classes\PSOActivityStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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


    public function createActivity(Request $request)
    {
        // todo this needs to go into a helper elf
        $tz = null;
        if ($request->time_zone) {
            $tz = '+' . $request->time_zone . ':00';
            if ($request->time_zone < 10 && $request->time_zone > -10) {
                $tz = $request->time_zone < 0 ? '-0' . abs($request->time_zone) . ':00' : '+0' . abs($request->time_zone) . ':00';
            }
        }

        $relative_day = $request->activity_id ?: 1;
        $hours_to_add = ($request->window_size ?: 0) == 0 ? 8 : ($request->window_size ?: 0);


        $activity_build_data = new Request([
            'activity_id' => $request->activity_id ?: Str::orderedUuid()->getHex()->toString(),
            'lat' => $request->lat,
            'long' => $request->long,
            'sla_start' => Carbon::now()->addDay($relative_day)->setTime(8, 0)->toDateTimeLocalString() . $tz,
            'sla_end' => Carbon::now()->addDay($relative_day)->setTime(8 + $hours_to_add, 0)->toDateTimeLocalString() . $tz,
            'sla_type_id' => $request->sla_type_id,
            'activity_type_id' => $request->activity_type_id,
            'status_id' => 0,
            'duration' => $request->duration
        ]);

        $activity = new PSOActivity($activity_build_data);

        $input_ref = (new InputReference(
            'Instant Activity Generator from the thingy',
            'CHANGE',
            $request->dataset_id,
            $request->input_datetime
        ))->toJson();

        $payload = $this->ActivityFullPayload($input_ref, $activity->FullActivityObject());

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, 'quick activity sent to PSO');


    }

    public function getActivity(Request $request, $activity_id): Collection
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

    public function activityExists(): bool
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
//            $activity_part_payload[] = $this->ActivityStatusPartPayload($suggestion['activity_id'], config('pso-services.statuses.commit_status'), $suggestion['resource_id'], $suggestion['expected_start_datetime'], 'From the Commit Service Thingy');
            $activity_part_payload[] = (new PSOActivityStatus(
                config('pso-services.statuses.commit_status'),
                1,
                0,
                true,
                $suggestion['resource_id'],
                'From the Commit Service Thingy',
                $suggestion['expected_start_datetime'])
            )->toJson($suggestion['activity_id']);
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

        $activity_part_payload = (
            new PSOActivityStatus(
                $status,
                1,
                0,
                false,
                $request->resource_id,
                'Update Status from the thingy',
                $request->date_time_fixed)
        )->toJson($request->activity_id);

//        $activity_part_payload = $this->ActivityStatusPartPayload(
//            $request->activity_id,
//            $pso_status,
//            $request->resource_id,
//            $request->date_time_fixed,
//            'From the change status thingy'
//        );

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

    private function ActivityFullPayload($input_reference, $activity_payload): array
    {

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Activity' => $activity_payload['Activity'],
                'Activity_Skill' => $activity_payload['Activity_Skill'],
                'Activity_SLA' => $activity_payload['Activity_SLA'],
                'Activity_Status' => $activity_payload['Activity_Status'],
                'Location' => $activity_payload['Location'],
            ]
        ];
    }


    private function ActivityStatusFullPayload($dataset_id, $activity_status_payload, $description): array
    {
        $input_ref = (new InputReference($description, 'Change', $dataset_id))->toJson();

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Activity_Status' => $activity_status_payload,

            ]
        ];
    }

    // no longer needed
    /*
    private function ActivityStatusPartPayload($activity_id, $status, $resource_id, $date_time_fixed, $reason): array
    {
        // all instances of this should use PSOActivityStatus

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

    } */


    public function deleteActivity(Request $request, $description = null)
    {
        $delete_data =  ['object_type_id' => 'activity', 'object_pk_name1' => 'id', 'object_pk1' => $request->activity_id];

        $delete_activity_payload = $this->DeleteObjectPart($delete_data);
        $payload = $this->DeleteObjectFull($delete_activity_payload, $request->dataset_id, 'Activity');

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $description);

    }

    public function deleteSLA(Request $request, $description = null): JsonResponse
    {
        // build the payload
        $delete_data = new Request([
            'object_type_id' => 'Activity_SLA',
            'object_pk_name1' => 'activity_id',
            'object_pk1' => $request->activity_id,
            'object_pk_name2' => 'sla_type_id',
            'object_pk2' => $request->sla_type_id,
            'object_pk_name3' => 'priority',
            'object_pk3' => $request->priority?:1,
            'object_pk_name4' => 'start_based',
            'object_pk4' => (bool)$request->start_based,
            ]);
$delete_sla_payload=$this->deleteActivity($delete_data);
        //        $delete_sla_payload = $this->DeleteSLAPayloadPart($request->activity_id, $request->sla_type_id, $request->priority, $request->start_based);
        // build the full payload
        $payload = $this->DeleteObjectFull($delete_sla_payload, $request->dataset_id, 'SLA');

        return $this->IFSPSOAssistService->processPayload(true, $payload, $this->token, $request->base_url, $description);

    }


    private function DeleteObjectFull($payload, $dataset_id, $description): array
    {

        $input_ref = (new InputReference(("Deleting " . $description . " from the thingy"), 'CHANGE', $dataset_id))->toJson();

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Object_Deletion' => $payload
            ]
        ];
    }

    private function DeleteObjectPart($delete_data)
    {

        // this input expects an array of types + names + pks

        $additional_pk = [
            'pk2' => [
                'name' => 'object_pk_name2',
                'pk' => 'object_pk2'
            ],
            'pk3' => [
                'name' => 'object_pk_name3',
                'pk' => 'object_pk3'
            ],
            'pk4' => [
                'name' => 'object_pk_name4',
                'pk' => 'object_pk4'
            ],
        ];

        $delete_object =
            [
                'object_type_id' => $delete_data['object_type_id'],
                'object_pk_name1' => $delete_data['object_pk_name1'],
                'object_pk1' => $delete_data['object_pk1']
            ];

        foreach ($additional_pk as $pk) {
            if (Arr::has($delete_data, $pk['name'])) {
                $delete_object = Arr::add($delete_object, $pk['name'], $delete_data[$pk['pk']]);
            }
        }

        return $delete_object;

    }
    // no longer needed
    /*
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
    */
}
