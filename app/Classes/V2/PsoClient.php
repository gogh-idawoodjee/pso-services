<?php

namespace App\Classes\V2;

use App\Constants\PSOConstants;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Helpers\HttpErrorMapper;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\SourceData;
use App\Helpers\Stubs\SourceDataParameter;
use App\Helpers\UrlHelper;
use App\Traits\V2\ApiResponses;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use Log;
use SensitiveParameter;
use Throwable;

class PsoClient
{
    use ApiResponses;

    public function sendToPso(
        array $payload,
        array $environmentData,
        #[SensitiveParameter] string $sessionToken,
        PsoEndpointSegment $segment,
    ): JsonResponse {
        $totalTimeout = (int) config('pso-services.defaults.timeout', 10);
        $connectTimeout = min(3, max(1, $totalTimeout - 1));
        $cid = (string) Str::uuid();

        try {
            $baseUrl = UrlHelper::normalizeBaseUrl(data_get($environmentData, 'baseUrl'));
            $url = "{$baseUrl}/IFSSchedulingRESTfulGateway/api/v1/scheduling/{$segment->value}";

            Log::info('PSO POST', ['url' => $url, 'segment' => $segment->value]);

            $response = Http::timeout($totalTimeout)
                ->connectTimeout($connectTimeout)
                ->withHeaders(['apiKey' => $sessionToken])
                ->post($url, $payload);

            return $this->handleDataResponse($response);
        } catch (ConnectionException $e) {
            return HttpErrorMapper::fromConnectionException($e, $url ?? null, $cid);
        } catch (Throwable $e) {
            return $this->error([
                'error' => 'Request could not be dispatched',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    public function getPsoData(
        string $datasetId,
        string $baseUrl,
        #[SensitiveParameter] string $sessionToken,
        PsoEndpointSegment $segment,
        string|null $resourceId = null,
        bool $includeInput = false,
        bool $includeOutput = false,
        string|null $minDate = null,
        string|null $maxDate = null,
    ): JsonResponse {
        $totalTimeout = (int) config('pso-services.defaults.timeout', 10);
        $connectTimeout = min(3, max(1, $totalTimeout - 1));
        $cid = (string) Str::uuid();

        try {
            $base = UrlHelper::normalizeBaseUrl($baseUrl);
            $endpoint = '/IFSSchedulingRESTfulGateway/api/v1/scheduling/' . $segment->value;

            $queryParams = compact('datasetId');
            if ($includeInput) {
                $queryParams['includeInput'] = 'true';
            }
            if ($includeOutput) {
                $queryParams['includeOutput'] = 'true';
            }
            if ($resourceId) {
                $queryParams['resourceId'] = $resourceId;
            }
            if ($minDate) {
                $queryParams['minimumDateTime'] = PSOHelper::toUrlEncodedIso8601($minDate);
            }
            if ($maxDate) {
                $queryParams['maximumDateTime'] = PSOHelper::toUrlEncodedIso8601($maxDate);
            }

            $url = "{$base}{$endpoint}?" . http_build_query($queryParams);

            Log::info('PSO GET', ['url' => $url, 'segment' => $segment->value]);

            $response = Http::timeout($totalTimeout)
                ->connectTimeout($connectTimeout)
                ->withHeaders(['apiKey' => $sessionToken])
                ->get($url);

            return $this->handleDataResponse($response);
        } catch (ConnectionException $e) {
            return HttpErrorMapper::fromConnectionException($e, $url ?? null, $cid);
        } catch (Throwable $e) {
            return $this->error([
                'error' => 'Request could not be dispatched',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    public function buildPayload(array $payloadItems, int $psoApiVersion = 1, bool $useWrapper = false): array
    {
        if ($psoApiVersion === 1) {
            $data = [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
            ];

            foreach ($payloadItems as $itemKey => $itemValues) {
                $data[$itemKey] = $itemValues;
            }

            if ($useWrapper) {
                return [
                    'payloadToPso' => ['dsScheduleData' => $data],
                ];
            }

            return [
                'dsScheduleData' => $data,
            ];
        }

        return $payloadItems;
    }

    public function sendOrSimulate(
        array $payload,
        array $environmentData,
        string|null $sessionToken,
        bool|null $requiresRotaUpdate = null,
        string|null $rotaUpdateDescription = null,
        string|null $additionalDetails = null,
        bool|null $addInputReference = null,
        string|null $inputReferenceDescription = null,
        string|null $resultsUrl = null,
    ): JsonResponse {
        if ($addInputReference) {
            $payload['Input_Reference'] =
                InputReferenceBuilder::make(data_get($environmentData, 'datasetId'))
                    ->inputType(InputMode::CHANGE)
                    ->description($inputReferenceDescription)
                    ->build();
        }

        $psoPayload = $this->buildPayload($payload);
        $wrappedPayload = $this->buildPayload($payload, 1, true);

        if ($sessionToken) {
            $psoResponse = $this->sendToPso(
                $psoPayload,
                $environmentData,
                $sessionToken,
                PsoEndpointSegment::DATA,
            );

            if ($requiresRotaUpdate) {
                $rotaUpdatePayload['Input_Reference'] =
                    InputReferenceBuilder::make(data_get($environmentData, 'datasetId'))
                        ->inputType(InputMode::CHANGE)
                        ->description($rotaUpdateDescription)
                        ->build();
                $rotaUpdatePayload['Source_Data'] = SourceData::make();
                $rotaUpdatePayload['Source_Data_Parameter'] = SourceDataParameter::make(
                    PSOConstants::SOURCE_DATA_PARAM_NAME,
                    PSOConstants::SOURCE_DATA_PARAM_VALUE,
                );

                $this->sendToPso(
                    $this->buildPayload($rotaUpdatePayload),
                    $environmentData,
                    $sessionToken,
                    PsoEndpointSegment::DATA,
                );
            }

            if ($psoResponse->status() < 400) {
                return $this->sentToPso(
                    ['payloadToPso' => $wrappedPayload['payloadToPso'], 'responseFromPso' => $psoResponse->getData()],
                    $additionalDetails,
                    $resultsUrl,
                );
            }

            return $psoResponse;
        }

        return $this->notSentToPso($wrappedPayload, $additionalDetails);
    }

    public function sendOrSimulateBuilder(): SendOrSimulateBuilder
    {
        return new SendOrSimulateBuilder($this);
    }

    private function handleDataResponse(Response $response): JsonResponse
    {
        if ($response->successful()) {
            return response()->json($response->json());
        }

        return $this->handleErrorResponse($response);
    }

    private function handleErrorResponse(Response $response): JsonResponse
    {
        $statusCode = $this->adjustStatusCode($response);
        $errorDetails = $this->parseResponseBody($response->body());

        return $this->error([
            'error' => $this->getErrorMessage($statusCode),
            'details' => $errorDetails,
        ], $statusCode);
    }

    private function getErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            401 => 'Unauthorized. Please check your session or login credentials.',
            400 => 'Client Error. See Details.',
            404 => 'URL not found. Check the endpoint.',
            500 => 'Internal server error. Try again later.',
            default => 'Unexpected error.',
        };
    }

    private function parseResponseBody(string|null $body): mixed
    {
        if (empty($body)) {
            return null;
        }

        try {
            return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $body;
        }
    }

    private function adjustStatusCode(Response $response): int
    {
        $statusCode = $response->status();

        if ($statusCode === 400 && !empty($response->body())) {
            try {
                $errorDetails = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

                if (is_object($errorDetails) && data_get($errorDetails, 'Message') === 'AUTHENTICATION_FAILED') {
                    return 401;
                }
            } catch (JsonException) {
                // Keep original status code
            }
        }

        return $statusCode;
    }
}
