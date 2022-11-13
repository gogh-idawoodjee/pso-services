<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSOActivity;
use App\Classes\PSOActivityStatus;
use App\Classes\PSODeleteObject;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $tz = Helper::setTimeZone($request->time_zone);

        $relative_day = $request->relative_day ?: 1;
        $relative_day_end = $request->relative_day_end ?: $relative_day;
        $hours_to_add = ($request->window_size ?: 0) == 0 ? 8 : ($request->window_size ?: 0);


        $activity_build_data = new Request([
            'activity_id' => $request->activity_id ?: Str::orderedUuid()->getHex()->toString(),
            'lat' => $request->lat,
            'long' => $request->long,
            'sla_start' => Carbon::now()->addDay($relative_day)->setTime(8, 0)->toDateTimeLocalString() . $tz,
            'sla_end' => Carbon::now()->addDay($relative_day_end)->setTime(8 + $hours_to_add, 0)->toDateTimeLocalString() . $tz,
            'sla_type_id' => $request->sla_type_id,
            'activity_type_id' => $request->activity_type_id,
            'status_id' => 0,
            'duration' => $request->duration,
            'description' => $request->description ?: 'Instant Activity from the thingy',
            'skill' => $request->skill,
            'region' => $request->region
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

    public function getActivity(Request $request, $activity_id)//: Collection
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
        return $this->activity_object = $activity->collect()->first();
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
        // why did I type this?
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

        $payload = $this->ActivityStatusFullPayload($request->dataset_id, $activity_part_payload, 'Status Change from the thingy');

        $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, 'Status Change from the thingy');

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
                'Location_Region' => $activity_payload['Location_Region'],
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


    public function deleteActivity(Request $request, $description = null)
    {

        $delete_activity_payload = (new PSODeleteObject(
            'Activity',
            'id', $request->activity_id
        ))->toJson();
        $payload = $this->DeleteObjectFull($delete_activity_payload, $request->dataset_id, 'Activity');

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $description);

    }

    public function deleteSLA(Request $request, $description = null)//: JsonResponse
    {

        $delete_sla_payload = (new PSODeleteObject(
            'Activity_SLA',
            'activity_id', $request->activity_id,
            'sla_type_id', $request->sla_type_id,
            'priority', $request->priority ?: 1,
            'start_based', (bool)$request->start_based
        ))->toJson();

        $payload = $this->DeleteObjectFull($delete_sla_payload, $request->dataset_id, 'SLA');

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $description);

    }


    private function DeleteObjectFull($payload, $dataset_id, $description): array
    {

        $input_ref = (
        new InputReference(("Deleting " . $description . " from the thingy"),
            'CHANGE',
            $dataset_id))
            ->toJson();

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Object_Deletion' => $payload
            ]
        ];
    }

}
