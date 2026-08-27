<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\BroadcastBuilder;
use App\Classes\V2\EntityBuilders\BroadcastParameterBuilder;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Classes\V2\PsoClient;
use App\Constants\PSOConstants;
use App\DataTransferObjects\PsoContext;
use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;
use App\Enums\BroadcastType;
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
        $sendToPso = data_get($environment, 'sendToPso', false);
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

        $payload = array_merge($payload, $this->buildBroadcastsPayload($context->data('broadcasts', [])));

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

        $payload = array_merge($payload, $this->buildBroadcastsPayload($context->data('broadcasts', [])));

        return $this->psoClient->sendOrSimulateBuilder()
            ->payload($payload)
            ->environment($context->environment())
            ->psoApiVersion($context->psoApiVersion())
            ->token($context->token)
            ->send();
    }

    /**
     * Build Broadcast + Broadcast_Parameter entities from validated data.broadcasts[].
     *
     * Returns an empty array when there are no broadcasts. Broadcast is a
     * single object when there's exactly one broadcast (matching PSO's JSON
     * convention of a bare object for single rows) or a list when there are
     * several; Broadcast_Parameter is always a flattened list across all of them.
     */
    protected function buildBroadcastsPayload(array $broadcasts): array
    {
        if (empty($broadcasts)) {
            return [];
        }

        $built = array_map(function (array $broadcast) {
            $parameters = array_map(
                static fn (array $param) => BroadcastParameterBuilder::make()
                    ->name($param['name'])
                    ->value($param['value']),
                $broadcast['parameters'] ?? [],
            );

            $builder = BroadcastBuilder::make()
                ->id($broadcast['id'] ?? null)
                ->active($broadcast['active'] ?? true)
                ->type(BroadcastType::from($broadcast['broadcastTypeId'])->value)
                ->planType(BroadcastPlanType::from($broadcast['planType']))
                ->description($broadcast['description'] ?? null)
                ->onceOnly($broadcast['onceOnly'] ?? false)
                ->minimumPlanQuality($broadcast['minimumPlanQuality'] ?? null)
                ->minimumStepInterval($broadcast['minimumStepInterval'] ?? null)
                ->expiryDatetime($broadcast['expiryDatetime'] ?? null)
                ->inputReferenceId($broadcast['inputReferenceId'] ?? null)
                ->maximumFrequency($broadcast['maximumFrequency'] ?? null)
                ->maximumWait($broadcast['maximumWait'] ?? null)
                ->minimumVisitStatus($broadcast['minimumVisitStatus'] ?? null)
                ->timeFilterStart($broadcast['timeFilterStart'] ?? null)
                ->timeFilterEnd($broadcast['timeFilterEnd'] ?? null)
                ->parameters($parameters);

            if (! empty($broadcast['allocationType'])) {
                $builder->allocationType(array_map(
                    static fn (int $value) => BroadcastAllocationType::from($value),
                    $broadcast['allocationType'],
                ));
            }

            return $builder->build();
        }, $broadcasts);

        return [
            'Broadcast' => count($built) === 1
                ? $built[0]['Broadcast']
                : array_column($built, 'Broadcast'),
            'Broadcast_Parameter' => array_merge(...array_column($built, 'Broadcast_Parameter')),
        ];
    }
}
