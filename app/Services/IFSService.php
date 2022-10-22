<?php

namespace App\Services;

use App\Models\PsoEnvironment;

class IFSService

{

    protected PsoEnvironment $pso_environment;
    protected $token;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {

        // has token, don't do anything
        $this->token = $token;
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

        // if we need auth, then there's two ways
        // no token and user/pass exists (this must be true because of validation logic)
        //      - send_to_pso = true
        //      - must have token or user/pass
        if ($requires_auth && !$token) {
            $this->authenticatePSO($base_url, $account_id, $username, $password);

        }

    }

    private function authenticatePSO($base_url, $account_id, $username, $password)
    {
        $this->token = (new IFSAuthService($base_url, $account_id, $username, $password))->getToken('pso');
    }
}
