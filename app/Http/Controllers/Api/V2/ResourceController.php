<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowResourceRequest;
use App\Traits\V2\PSOAssistV2;

class ResourceController extends Controller
{

    use PSOAssistV2;

    /**
     * Display the specified resource.
     */
    public function show(ShowResourceRequest $request, string $resourceId)
    {

        return $request->headers->all();

    }

}
