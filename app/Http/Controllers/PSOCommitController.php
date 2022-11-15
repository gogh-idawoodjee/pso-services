<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOActivityService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PSOCommitController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        // receive the payload
        // strip out the nastiness


        if (!$request->all()) {
            $content = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $request->getContent()));
        } else {
            $content = $request->all();
        }

        $commit = new IFSPSOActivityService(
            config('pso-services.debug.base_url'),
            null,
            config('pso-services.debug.username'),
            env('PSO_PASSWORD'),
            config('pso-services.debug.account_id'),
            true,
            null
        );


        return $commit->sendCommitActivity($content);

    }

    public function store(Request $request): JsonResponse
    {
        // strip out the nastiness

        if (!$request->all()) {
            $content = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $request->getContent()));
        } else {
            $content = $request->all();
        }

        $commit = new IFSPSOActivityService(null, null, null, null, null, false, null);

        if ($commit->isAuthenticated()) {
            return $commit->sendCommitActivity($content, true);
        }

        return response()->json([
            'status' => 401,
            'description' => 'did not pass auth'
        ]);


    }


}
