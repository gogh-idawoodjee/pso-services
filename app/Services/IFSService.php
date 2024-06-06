<?php

namespace App\Services;

use App\Models\PsoEnvironment;
use Exception;
use Illuminate\Support\Facades\Http;
use App\Helpers\PSOHelper;

class IFSService

{
    protected PsoEnvironment $pso_environment;
    // todo make sure this need to be protected and not public
    protected string|null $token;
    private string|null $base_url;
    protected string $service_name;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {

        $this->token = $token;
        $this->base_url = $base_url;
        $this->service_name = config('pso-services.settings.service_name');

        if (!$pso_environment) {
            $this->pso_environment = new PsoEnvironment();
            $this->pso_environment->base_url = null;
        }

        // below will overwrite if needed

        if ($pso_environment) {
            // local mode, covered
            $this->pso_environment = (new PsoEnvironment)->find($pso_environment);
            $this->authenticatePSO($this->pso_environment->base_url, $this->pso_environment->account_id, $this->pso_environment->username, $this->pso_environment->password);
            return;
        }

        if ($requires_auth && !$this->token) {

            $this->authenticatePSO($base_url, $account_id, $username, $password);
        }


    }

    private function authenticatePSO($base_url, $account_id, $username, $password)
    {

        $response = collect();
        if ($base_url) {

            try {

                $response = Http::asForm()
                    ->timeout(PSOHelper::GetTimeOut())
                    ->connectTimeout(PSOHelper::GetTimeOut())
                    ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session', [
                        'accountId' => $account_id,
                        'userName' => $username,
                        'password' => $password,
                    ]);


            } catch (Exception) {

                // todo need to catch this fail and bubble it up to is_authenticated
            }
        }

        if ($response->collect()->get('SessionToken')) {
            return $this->token = $response->collect()->get('SessionToken');
        }
    }

    public function isAuthenticated()
    {
        if ($this->token) {
            return $this->validateToken($this->base_url, $this->token);
        }
        return false;
    }

    private function validateToken($base_url, $token): bool
    {
        try {
            $response = Http::withHeaders(['apiKey' => $token])->get($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session');

            if ($response->failed()) {
                return false;
            }
            return true;
        } catch (Exception) {
            return false;
        }

    }
}
