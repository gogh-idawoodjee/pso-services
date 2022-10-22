<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOActivityService;

use Illuminate\Http\Request;


class PSOCommitController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // receive the payload
        // stripout the nastiness


        if (!$request->all()) {
            $content = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $request->getContent()));
        } else {
            $content = $request->all();
        }
        // chunk it out here or in the service? probably in the service
//        $suggestions = $content->Suggested_Dispatch;
        // send the chunk to the service

        $commit = new IFSPSOActivityService('cb847e5e-8747-4a02-9322-76530ef38a19');

        return $commit->sendCommitActivity($content);

        // push it to webhook for us to see
//        $pso_resource = Http::patch('https://webhook.site/55a3b912-bdfb-4dd9-ad84-c1bcb55e92c3', $content);

    }


}
