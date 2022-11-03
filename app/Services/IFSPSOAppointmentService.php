<?php

namespace App\Services;

use App\Classes\PSOActivity;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IFSPSOAppointmentService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function getAppointment(Request $request): JsonResponse
    {

        $payload = null;
        $activity = PSOActivity::create();

        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso, $payload, $this->token, $request->base_url, 'Event Set and Rota Updated', false, $request->dataset_id,
        );
    }


}
