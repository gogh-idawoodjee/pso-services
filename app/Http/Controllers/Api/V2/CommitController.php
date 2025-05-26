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
        //  ****trash**** so this bad boy needs to be able to lookup environments in PSO-Test-Tools ****trash****
        // scratch that line above, just pass the environment data in, lolzers!!
        // no that won't work bruv, because if you're using the API directly, you won't be able send creds... you do have to look it up
        // untrash the above
        // basically each environment becomes it's own commit endpoint /api/v2/commit/{environment}
        // we grab the dataset from the SDS request and use the authentication details from the environment
        // won't be able to get the password because it's encrypted;
        //              we need a shared key between these two dudes to re-encrypt the password during transmission and decrypt it here
        // going to have to be in the .env and pulled in via config

        return true;
    }

}
