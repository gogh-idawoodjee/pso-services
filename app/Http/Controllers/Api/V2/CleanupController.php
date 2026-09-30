<?php

namespace App\Http\Controllers\Api\V2;

use App\Classes\AuthenticatedPsoActionService;
use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\OkResponse;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\CleanupRequest;
use App\Services\V2\CleanupService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class CleanupController extends Controller
{
    use PSOAssistV2;

    /**
     * Clean Up Expired Activities
     *
     * Deletes activities whose most recent allocation ended at or before the
     * cutoff (defaults to the start of today). Unlike other V2 endpoints,
     * this always authenticates and always commits the deletion — deciding
     * what to delete requires reading the live schedule first, so there is
     * no dry-run/preview mode.
     */
    #[OkResponse(data: 'array{cleanupSummary: array{activitiesDeleted: int, activityIds: list<string>}, responseFromPso?: object}', description: 'Expired activities deleted, or nothing to clean up', examples: [
        ['data' => ['cleanupSummary' => ['activitiesDeleted' => 2, 'activityIds' => ['ACT-001', 'ACT-002']], 'responseFromPso' => new \stdClass], 'message' => SentToPso::MESSAGE],
        ['data' => ['cleanupSummary' => ['activitiesDeleted' => 0, 'activityIds' => []]], 'message' => 'Nothing to clean up.'],
    ])]
    public function destroy(CleanupRequest $request, CleanupService $cleanupService): JsonResponse
    {
        $authDetails = $this->getAuthDetails($request);
        $authDetails['sendToPso'] = true; // always authenticate — cleanup has no preview mode

        return app(AuthenticatedPsoActionService::class)->run(
            $authDetails,
            function (array $auth) use ($request, $cleanupService) {
                $request->merge([
                    'environment' => array_merge((array) $request->input('environment', []), ['token' => data_get($auth, 'token')]),
                ]);

                return $cleanupService->cleanupDataset(PsoContext::fromRequest($request));
            }
        );
    }
}
