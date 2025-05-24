<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;


use App\Models\V2\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
        // won't be able to get the password because it's encrypted;
        //              we need a shared key between these two dudes to re-encrypt the password during transmisison and decrypt it here
        // going to have to be in the .env and pulled in via config
        Crypt::encrypt($value, $key);
        return true;
    }

}
