<?php

namespace App\Services\V2;

use App\Classes\AuthenticatedPsoActionService;
use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Enums\ActivityStatus;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use App\Models\V2\PSOCommitLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Receives PSO's "Suggested Dispatch" broadcast and commits it back to PSO
 * as Activity_Status updates.
 *
 * Unlike every other V2 service, this is invoked by PSO calling us — not the
 * other way around — so there's no per-request environment block to read
 * credentials from. Authentication always uses the pre-configured server
 * credentials in pso-services.debug.*.
 */
class CommitService extends BaseService
{
    public function commit(array $broadcast): JsonResponse
    {
        try {
            $suggestions = $this->normalizeSuggestions(data_get($broadcast, 'Suggested_Dispatch'));

            if (empty($suggestions)) {
                return $this->ok([], 'No suggested dispatch to commit');
            }

            $datasetId = data_get($broadcast, 'Plan.0.dataset_id');

            $authDetails = [
                'sendToPso' => true,
                'baseUrl' => config('pso-services.debug.base_url'),
                'accountId' => config('pso-services.debug.account_id'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password'),
            ];

            return app(AuthenticatedPsoActionService::class)->run(
                $authDetails,
                fn(array $auth) => $this->sendCommit($auth, $datasetId, $suggestions)
            );
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    private function sendCommit(array $auth, string $datasetId, array $suggestions): JsonResponse
    {
        $activityStatuses = collect($suggestions)
            ->map(fn(array $suggestion) => $this->buildActivityStatus($suggestion))
            ->all();

        $inputReference = InputReferenceBuilder::make($datasetId)
            ->inputType(InputMode::CHANGE)
            ->datetime(data_get($suggestions[0], 'date_time_status'))
            ->description(
                'Committing ' . count($activityStatuses)
                . (count($activityStatuses) > 1 ? ' Activities' : ' activity')
                . ' based on the SDS'
            )
            ->build();

        $payload = $this->psoClient->buildPayload([
            'Input_Reference' => $inputReference,
            'Activity_Status' => $activityStatuses,
        ]);

        $response = $this->psoClient->sendToPso($payload, $auth, $auth['token'], PsoEndpointSegment::DATA);

        if (config('pso-services.settings.enable_commit_service_log')) {
            PSOCommitLog::create([
                'id' => Str::orderedUuid()->getHex()->toString(),
                'input_reference' => $inputReference['id'],
                'pso_suggestions' => $suggestions,
                'output_payload' => $payload,
                'pso_response' => $response->getData(true),
            ]);
        }

        if (config('pso-services.settings.enable_debug')) {
            Http::patch('https://webhook.site/' . config('pso-services.debug.webhook_uuid'), $payload);
        }

        if ($response->status() >= 400) {
            return $response;
        }

        return $this->sentToPso(['payloadToPso' => $payload, 'responseFromPso' => $response->getData()]);
    }

    private function buildActivityStatus(array $suggestion): array
    {
        $start = Carbon::parse(data_get($suggestion, 'expected_start_datetime'));
        $end = Carbon::parse(data_get($suggestion, 'expected_end_datetime'));

        return ActivityStatusBuilder::make(data_get($suggestion, 'activity_id'), ActivityStatus::COMMITTED)
            ->resourceId(data_get($suggestion, 'resource_id'))
            ->fixed(data_get($suggestion, 'fixed_resource'))
            ->visitId(data_get($suggestion, 'visit_id'))
            ->duration((string) abs($start->diffInMinutes($end)))
            ->reason('From the Commit Service via ' . config('pso-services.settings.service_name'))
            ->dateTimeFixed(
                config('pso-services.settings.fix_committed_activities')
                    ? data_get($suggestion, 'expected_start_datetime')
                    : null
            )
            ->timestampOverride(
                config('pso-services.settings.override_commit_timestamps')
                    ? config('pso-services.settings.override_commit_timestamp_value')
                    : null
            )
            ->build();
    }

    /**
     * PSO sends either a single suggestion object or an array of them.
     */
    private function normalizeSuggestions(mixed $suggestions): array
    {
        if (empty($suggestions)) {
            return [];
        }

        return array_is_list($suggestions) ? $suggestions : [$suggestions];
    }
}
