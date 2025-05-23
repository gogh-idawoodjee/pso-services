<?php

namespace App\Http\Controllers;

use App\Services\V1\IFSPSOActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonException;

class PSOCommitController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function update(Request $request)
    {

        Log::info('committing');
        $commit = new IFSPSOActivityService(
            config('pso-services.debug.base_url'),
            null,
            config('pso-services.debug.username'),
            config('pso-services.debug.password'),
            config('pso-services.debug.account_id'),
            true,
            null
        );

        return $commit->sendCommitActivity($request->all(), config('pso-services.settings.enable_debug'));

    }

    /**
     * @throws JsonException
     */
    public function store(Request $request): JsonResponse
    {

        $commit = new IFSPSOActivityService(null, null, null, null, null, false, null);

        if ($commit->isAuthenticated()) {
            return $commit->sendCommitActivity($request->all(), true);
        }

        return response()->json([
            'status' => 401,
            'description' => 'did not pass auth'
        ]);

    }

}
