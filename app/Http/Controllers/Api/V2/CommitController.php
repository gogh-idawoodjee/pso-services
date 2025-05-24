<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;


use App\Models\V2\Environment;
use Illuminate\Http\Request;

class CommitController extends Controller
{

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Environment $environment)
    {
        // so this bad boy needs to be able to lookup environments in PSO-Test-Tools
        // basically each environment becomes it's own commit endpoint /api/v2/commit/{environment}
        // we grab the dataset from the SDS request and use the authentication details from the environment
        return true;
    }

}
