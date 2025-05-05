<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\InputMode;
use App\Enums\ProcessType;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\InputReference;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

class LoadService extends BaseService
{


    protected array $data;

    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {
        parent::__construct($sessionToken, $data);
        $this->data = $data;
    }

    /**
     * @throws ConnectionException
     */
    public function loadPSO(): JsonResponse
    {

        $datasetId = data_get($this->data, 'environment.datasetId');
        $datetime = data_get($this->data, 'data.datetime');
        $dseDuration = PSOHelper::setPSODurationDays(data_get($this->data, 'data.dseDuration'));
        $processType = Processtype::from(data_get($this->data, 'data.processType'));
        $appointmentWindow = data_get($this->data, 'data.appointmentWindow') ? PSOHelper::setPSODurationDays(data_get($this->data, 'data.appointmentWindow')) : null;
        $id = data_get($this->data, 'data.Id');
        $description = data_get($this->data, 'data.description');
        $payload = InputReference::make($datasetId, InputMode::LOAD, $datetime, $dseDuration, $processType, $appointmentWindow, $id, $description);
        $keepPsoData = data_get($this->data, 'data.keepPsoData');
        $sendToPso = data_get($this->data, 'data.sendToPso');
        $keepPsoDataMessage = null;

        $baseUrl = data_get($this->data, 'environment.baseUrl');

        if ($keepPsoData && $sendToPso) {
            $keepPsoDataMessage = 'Keeping Existing PSO Data';

            // todo test this - we have to assume we have the token because sendToPSO is true
            $scheduleData = ScheduleService::getScheduleData($baseUrl, $datasetId, $this->sessionToken);
            $payload = collect($payload)->merge($scheduleData)->toArray();

        }
        if ($keepPsoData && !$sendToPso) {
            $keepPsoDataMessage = 'Attention: Request to Keep PSO Data but not sending to PSO.';
        }

        if ($this->sessionToken) {
            // call sendToPso method
        }

        return $this->notSentToPso(($this->buildPayload(['Input_Reference' => $payload], 1, true)), $keepPsoDataMessage);
    }

    public function updateRota(): JsonResponse
    {

        $datasetId = data_get($this->data, 'environment.datasetId');
        $datetime = data_get($this->data, 'data.datetime');
        $id = data_get($this->data, 'data.Id');
        $description = data_get($this->data, 'data.description');
        $payload = InputReference::make($datasetId, InputMode::CHANGE, $datetime, null, null, null, $id, $description);

        if ($this->sessionToken) {
            // call sendToPso method
        }

        return $this->notSentToPso(($this->buildPayload(['Input_Reference' => $payload], 1, true)));
    }


}
