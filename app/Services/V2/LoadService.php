<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Classes\V2\PsoClient;
use App\Constants\PSOConstants;
use App\DataTransferObjects\PsoContext;
use App\Enums\InputMode;
use App\Enums\ProcessType;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\SourceData;
use App\Helpers\Stubs\SourceDataParameter;
use Illuminate\Http\JsonResponse;
use JsonException;

class LoadService extends BaseService
{
    public function __construct(PsoClient $psoClient, protected ScheduleService $scheduleService)
    {
        parent::__construct($psoClient);
    }

    /**
     * @throws JsonException
     */
    public function loadPSO(PsoContext $context): JsonResponse
    {
        $environment = $context->environment();
        $datasetId = data_get($environment, 'datasetId');
        $baseUrl = data_get($environment, 'baseUrl');

        $datetime = $context->data('datetime');
        $id = $context->data('Id');
        $description = $context->data('description');
        $dseDuration = PSOHelper::setPSODurationDays($context->data('dseDuration'));
        $processType = ProcessType::from($context->data('processType'));
        $appointmentWindowRaw = $context->data('appointmentWindow');
        $appointmentWindow = $appointmentWindowRaw ? PSOHelper::setPSODurationDays($appointmentWindowRaw) : null;
        $includeArpData = $context->data('includeArpData', false);
        $keepPsoData = $context->data('keepPsoData', false);
        $sendToPso = $context->data('sendToPso', false);
        $rotaId = $context->data('rotaId');

        $inputRef = InputReferenceBuilder::make($datasetId)
            ->inputType(InputMode::LOAD)
            ->dateTime($datetime)
            ->dseDuration($dseDuration)
            ->processType($processType)
            ->appointmentWindow($appointmentWindow)
            ->id($id)
            ->description($description)
            ->build();

        $payload = ['Input_Reference' => $inputRef];

        if ($includeArpData) {
            $payload['Source_Data'] = SourceData::make();
            $payload['Source_Data_Parameter'] = SourceDataParameter::make(
                PSOConstants::SOURCE_DATA_PARAM_NAME,
                $rotaId ?? 'master',
            );
        }

        $keepPsoDataMessage = null;

        if ($keepPsoData) {
            if ($sendToPso) {
                $keepPsoDataMessage = 'Keeping Existing PSO Data';
                $scheduleData = $this->scheduleService->getScheduleData($baseUrl, $datasetId, $context->token);
                $payload = array_merge($payload, $scheduleData);
            } else {
                $keepPsoDataMessage = 'Attention: Request to Keep PSO Data but not sending to PSO.';
            }
        }

        return $this->psoClient->sendOrSimulateBuilder()
            ->payload($payload)
            ->environment($environment)
            ->psoApiVersion($context->psoApiVersion())
            ->token($context->token)
            ->additionalDetails($keepPsoDataMessage)
            ->send();
    }

    public function updateRota(PsoContext $context): JsonResponse
    {
        $datasetId = $context->datasetId();
        $datetime = $context->data('datetime');
        $id = $context->data('Id');
        $description = $context->data('description') ?? PSOConstants::UPDATE_ROTA_DESCRIPTION;

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

        return $this->psoClient->sendOrSimulateBuilder()
            ->payload($payload)
            ->environment($context->environment())
            ->psoApiVersion($context->psoApiVersion())
            ->token($context->token)
            ->send();
    }
}
