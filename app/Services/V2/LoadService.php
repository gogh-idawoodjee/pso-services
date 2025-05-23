<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Constants\PSOConstants;
use App\Enums\InputMode;
use App\Enums\ProcessType;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\SourceData;
use App\Helpers\Stubs\SourceDataParameter;
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
        // Extracting environment block
        $environment = data_get($this->data, 'environment');
        $datasetId = data_get($environment, 'datasetId');
        $baseUrl = data_get($environment, 'baseUrl');

        // Extracting data block
        $data = data_get($this->data, 'data');
        $datetime = data_get($data, 'datetime');
        $id = data_get($data, 'Id');
        $description = data_get($data, 'description');
        $dseDuration = PSOHelper::setPSODurationDays(data_get($data, 'dseDuration'));
        $processType = Processtype::from(data_get($data, 'processType'));
        $appointmentWindowRaw = data_get($data, 'appointmentWindow');
        $appointmentWindow = $appointmentWindowRaw ? PSOHelper::setPSODurationDays($appointmentWindowRaw) : null;
        $includeArpData = data_get($data, 'includeArpData', false);
        $keepPsoData = data_get($data, 'keepPsoData', false);
        $sendToPso = data_get($data, 'sendToPso', false);

        // Build Input Reference
        $inputRef = InputReferenceBuilder::make($datasetId)
            ->inputType(InputMode::LOAD)
            ->dateTime($datetime)
            ->dseDuration($dseDuration)
            ->processType($processType)
            ->appointmentWindow($appointmentWindow)
            ->id($id)
            ->description($description)
            ->build();

        // Start payload
        $payload = ['Input_Reference' => $inputRef];

        // Add optional ARP source data
        if ($includeArpData) {
            $payload['Source_Data'] = SourceData::make();
            $payload['Source_Data_Parameter'] = SourceDataParameter::make(
                PSOConstants::SOURCE_DATA_PARAM_NAME,
                PSOConstants::SOURCE_DATA_PARAM_VALUE
            );
        }

        // Handle keep/send flags
        $keepPsoDataMessage = null;


        if ($keepPsoData) {
            if ($sendToPso) {
                $keepPsoDataMessage = 'Keeping Existing PSO Data';
                $scheduleData = ScheduleService::getScheduleData($baseUrl, $datasetId, $this->sessionToken, true, true);
                $payload = array_merge($payload, $scheduleData);
            } else {
                $keepPsoDataMessage = 'Attention: Request to Keep PSO Data but not sending to PSO.';
            }
        }

        return $this->sendOrSimulateBuilder()
            ->payload($payload)
            ->environment($environment)
            ->token($this->sessionToken)
            ->additionalDetails($keepPsoDataMessage)
            ->send();
    }


    /**
     */
    public function updateRota(): JsonResponse
    {

        $datasetId = data_get($this->data, 'environment.datasetId');
        $datetime = data_get($this->data, 'data.datetime');
        $id = data_get($this->data, 'data.Id');
        $description = data_get($this->data, 'data.description') ?? PSOConstants::UPDATE_ROTA_DESCRIPTION;

        $payload = [
            'Input_Reference' => InputReferenceBuilder::make($datasetId)
                ->inputType(InputMode::CHANGE)
                ->dateTime($datetime)
                ->id($id)
                ->description($description)
                ->build(),

            'Source_Data' => SourceData::make(),

            'Source_Data_Parameter' => SourceDataParameter::make(
                PSOConstants::SOURCE_DATA_PARAM_NAME,
                PSOConstants::SOURCE_DATA_PARAM_VALUE,
            ),
        ];


        return $this->sendOrSimulateBuilder()
            ->payload($payload)
            ->environment(data_get($this->data, 'environment'))
            ->token($this->sessionToken)
            ->send();

    }


}
