<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Classes\PSOActivity;
use App\Classes\PSOActivityStatus;
use App\Classes\PSODeleteObject;
use App\Helpers\PSOHelper;
use App\Models\PSOCommitLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class IFSPSOActivityService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;
    private Collection $activity_object;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }


    public function createActivity(Request $request)
    {

        $tz = PSOHelper::setTimeZone($request->time_zone);

        $relative_day = $request->relative_day ?: 1;
        $relative_day_end = $request->relative_day_end ?: $relative_day;
        $hours_to_add = ($request->window_size ?: 0) == 0 ? 8 : ($request->window_size ?: 0);


        $activity_build_data = new Collection([
            'activity_id' => $request->activity_id ?: Str::orderedUuid()->getHex()->toString(),
            'lat' => $request->lat,
            'long' => $request->long,
            'sla_start' => Carbon::now()->addDays($relative_day)->setTime(8, 0)->toDateTimeLocalString() . $tz,
            'sla_end' => Carbon::now()->addDays($relative_day_end)->setTime(8 + $hours_to_add, 0)->toDateTimeLocalString() . $tz,
            'sla_type_id' => $request->sla_type_id,
            'activity_type_id' => $request->activity_type_id,
            'status_id' => 0,
            'duration' => $request->duration,
            'description' => $request->description ?: 'Instant Activity from ' . $this->service_name,
            'skill' => $request->skill,
            'region' => $request->region,
            'priority' => $request->priority,
            'base_value' => $request->base_value,
            'fixed' => $request->fixed,
            'visit_id'=>$request->visit_id,
            'resource_id'=>$request->resource_id,
        ]);

        $activity = new PSOActivity(json_decode($activity_build_data->toJson()));

        $input_ref = (new InputReference(
            'Instant Activity Generator from ' . $this->service_name,
            'CHANGE',
            $request->dataset_id,
            $request->input_datetime
        ))->toJson();

        $payload = $this->ActivityFullPayload($input_ref, $activity->FullActivityObject());

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, 'quick activity sent to PSO');

    }

    public function getActivity(Request $request, $activity_id, $dataset_id)//: Collection
    {

        $activity = Http::withHeaders(['apiKey' => $this->token])
            ->get($request->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/activity',
                [
                    'includeOutput' => true,
                    'datasetId' => $dataset_id,
                    'activityId' => $activity_id,
                    'tableFilter' => 'Activity, SLA_Type, Activity_SLA, Location, Activity_Status'
                ]
            );
        return $this->activity_object = collect($activity->collect()->first());
    }

    public function activityExists()//: bool
    {
        return collect($this->activity_object)->has('Activity');
    }

    public function getActivityID()
    {
        return $this->activity_object['Activity']['id'];
    }

    public function sendSWBResponse()
    {

    }

    public function sendCommitActivity($pso_sds_broadcast, $debug_mode = false)//: JsonResponse
    {

        // should be assumed that environment and auth for this service is pre-configured

        //$dataset_id = config('pso-services.debug.dataset_id'); //  this is wrong, dataset should be from the broadcast
        $base_url = config('pso-services.debug.base_url');
        $activity_part_payload = [];

        // chunk out just the suggested dispatch
        $suggestions = collect($pso_sds_broadcast)->get('Suggested_Dispatch');

        if (count($suggestions) > 0) {

            $dataset_id = collect($pso_sds_broadcast)->get('Plan')[0]['dataset_id'];

            // this is to check if the suggestions is an array of objects or an object
            if (isset($suggestions->plan_id)) $newsuggestions[] = $suggestions; else {
                $newsuggestions = $suggestions;
            }

            // build the individual status update
            foreach ($newsuggestions as $suggestion) {
                $input_reference_datetime = $suggestion['date_time_status'];
                $start = Carbon::parse($suggestion['expected_start_datetime']);
                $end = Carbon::parse($suggestion['expected_end_datetime']);
                $difference = $end->diffInMinutes($start);

                $activity_part_payload[] = (new PSOActivityStatus(
                    config('pso-services.statuses.commit_status'),
                    $suggestion['visit_id'],
                    $difference,
                    true,
                    $suggestion['resource_id'],
                    'From the Commit Service via ' . $this->service_name,
                    config("pso-services.settings.fix_committed_activities") ? $suggestion['expected_start_datetime'] : null,
                    null, //$suggestion['expected_start_datetime'],
                    $suggestion['expected_start_datetime']
                )
                )->toJson($suggestion['activity_id']);
            }


            // build the full payload
            $activity_status_payload = $this->ActivityStatusFullPayload(
                $dataset_id,
                $activity_part_payload,
                'Committing ' . count($activity_part_payload) . (count($activity_part_payload) > 1 ? ' Activities' : ' activity') . ' based on the SDS',
                $input_reference_datetime);
            $activity_status = Http::withHeaders(['apiKey' => $this->token])
                ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    $activity_status_payload
                );

            if (config('pso-services.settings.enable_commit_service_log')) PSOCommitLog::create([
                'id' => Str::orderedUuid()->getHex()->toString(),
                'input_reference' => $activity_status_payload['dsScheduleData']['Input_Reference']['id'],
                'pso_suggestions' => json_encode($newsuggestions),
                'output_payload' => json_encode($activity_status_payload),
                'pso_response' => $activity_status->body(),
                'response_time' => $activity_status->transferStats->getTransferTime(),
                'transfer_stats' => json_encode($activity_status->transferStats->getHandlerStats())
            ]);


            if ($debug_mode) {
                $pso_resource = Http::patch('https://webhook.site/' . config('pso-services.debug.webhook_uuid'), $activity_status_payload);
            }

            return response()->json([
                'status' => 200,
                'description' => 'Service has sent payload to PSO',
                'original_payload' => [$activity_status_payload]
            ], 200, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);


        }
    }

    public function updateActivityStatus($request, $status): JsonResponse
    {

        $pso_status = config('pso-services.statuses.all.' . $status);

        $activity_part_payload = (
        new PSOActivityStatus(
            $pso_status,
            1,
            0,
            false,
            $request->resource_id,
            'Update Status from ' . $this->service_name,
            $request->date_time_fixed)
        )->toJson($request->activity_id);

        $payload = $this->ActivityStatusFullPayload($request->dataset_id, $activity_part_payload, 'Status Change via ' . $this->service_name);

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, 'Status Change via ' . $this->service_name);

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


    private function ActivityStatusFullPayload($dataset_id, $activity_status_payload, $description, $datetime = null): array
    {
        $input_ref = (
        new InputReference(
            $description,
            'Change',
            $dataset_id,
            $datetime)
        )->toJson();

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
        $payload = $this->DeleteObjectFull($delete_activity_payload, $request->dataset_id, $description);

        return $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, $description);

    }

    public function deleteActivities(Request $request, $description = null)
    {
        foreach ($request->activities as $activity) {
            $delete_activity_payload[] = (new PSODeleteObject(
                'Activity',
                'id', $activity
            ))->toJson();
        }

        $payload = $this->DeleteObjectFull($delete_activity_payload, $request->dataset_id, $description);
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
        new InputReference(("Deleting " . $description . " via " . $this->service_name),
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
