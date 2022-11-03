<?php

namespace App\Services;

use App\Models\PsoEnvironment;
use Exception;
use Illuminate\Support\Facades\Http;

class IFSService

{
    protected PsoEnvironment $pso_environment;
    // todo make sure this is protected again
    public $token;
    private $base_url;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {

        // has token, don't do anything

        // todo validate token and return 401 if failed
        $this->token = $token;
        $this->base_url = $base_url;

        if (!$pso_environment) {
            $this->pso_environment = new PsoEnvironment();
            $this->pso_environment->base_url = null;
        }

        // below will overwrite if needed

        if ($pso_environment) {
            // local mode, covered
            $this->pso_environment = PsoEnvironment::find($pso_environment);
            $this->authenticatePSO($this->pso_environment->base_url, $this->pso_environment->account_id, $this->pso_environment->username, $this->pso_environment->password);
            return;
        }

        if ($requires_auth && !$this->token) {
            $this->authenticatePSO($base_url, $account_id, $username, $password);
        }


    }

    private function authenticatePSO($base_url, $account_id, $username, $password)
    {
        try {
            $response = Http::asForm()->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session', [
                'accountId' => $account_id,
                'username' => $username,
                'password' => $password,
            ]);
        } catch (Exception $e) {
            dd($e);
        }

        if ($response->collect()->get('SessionToken')) {
            return $this->token = $response->collect()->get('SessionToken');
        }
    }

    public function isAuthenticated()
    {
        return $this->validateToken($this->base_url, $this->token);
    }

    private function validateToken($base_url, $token): bool
    {
        $response = Http::withHeaders(['apiKey' => $token])->get($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session');
        if ($response->failed()) {
            return false;
        }
        return true;

    }
}
