<?php

namespace App\Services;

use App\Classes\InputReference;
use App\Helpers\Helper;
use Carbon\Carbon;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFSPSOAssistService extends IFSService
{

    private function SourceDataParameter($rota_id)
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
                'parameter_name' => 'rota_id',
                'parameter_value' => "$rota_id",
            ];
    }


    private function RotaToDSEPayload($dataset_id, $rota_id, $datetime = null): array
    {
        $input_reference = (new InputReference(
            "Update Rota from the Thingy",
            'CHANGE',
            $dataset_id,
            $datetime)
        )->toJson();

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter($rota_id ?: $dataset_id),
            ]
        ];
    }

    public function sendRotaToDSE($dataset_id, $rota_id, $base_url, $date = null, $send_to_pso = null): JsonResponse
    {
        $payload = $this->RotaToDSEPayload($dataset_id, $rota_id, $date);

        return $this->processPayload($send_to_pso, $payload, $this->token, $base_url, 'Updated Rota from the Thingy');

    }

    public function apiResponse($code, $description, $payload, $payload_desc = null, $additional_data = null): JsonResponse
    {
        // all other services will call this method for payloads
        if ($additional_data) {
            return response()->json([
                'status' => $code,
                'description' => $description,
                $additional_data['description'] => $additional_data['data'],
                $payload_desc ?: 'original_payload' => [$payload]
            ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }
        return response()->json([
            'status' => $code,
            'description' => $description,
            $payload_desc ?: 'original_payload' => [$payload]
        ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    private function SourceData(): array
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
            ];
    }

    private function initializePSOPayload(Request $request): array
    {

        $description = $request->description ?: 'Init from the Thingy';
        $datetime = $request->datetime ?: Carbon::now()->toAtomString();
        $dse_duration = Helper::setPSODurationDays($request->dse_duration); // this doesn't need the helper elf we're expecting a solid number of days only here
        if ($request->appointment_window) {
            $appointment_window = Helper::setPSODurationDays($request->appointment_window);
        } else {
            $appointment_window = null;
        }
        $process_type = $request->process_type ?: config('pso-services.defaults.process_type');
        $rota_id = $request->rota_id ?: $request->dataset_id;


        $input_ref = (new InputReference(
            $description,
            'LOAD',
            $request->dataset_id,
            $datetime,
            $dse_duration,
            $process_type,
            $appointment_window)
        )->toJson();

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_ref,
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter($rota_id),
            ]
        ];
    }

    public function InitializePSO(Request $request)
    {

        $payload = $this->initializePSOPayload($request);

        return $this->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url, 'Initialize From the Thingy');


    }

    public function getUsageData($request)
    {

        $mindate = $request->mininum_date ?: Carbon::now()->format('Y-m-d');
        $maxdate = $request->maximum_date ?: Carbon::now()->add(1, 'day')->format('Y-m-d');

        $usage = Http::withHeaders([
            'apiKey' => $this->token
        ])->get(
            $request->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/usage',
            [
                'minimumDateTime' => $mindate,
                'maximumDateTime' => $maxdate
            ]);


        if ($usage->collect()->first()) {

            $mystuff = collect($usage->collect()->first())->map(function ($item) {

                $type = match ($item['ScheduleDataUsageType']) {
                    0 => 'Resource_Count',
                    1 => 'Activity_Count',
                    2 => 'DSE_Window',
                    3 => 'ABE_Window',
                    4 => 'Dataset_Count',
                };

                return collect($item)->put('count_type', $type);
            })->mapToGroups(function ($item, $key) {

                return [$item['DatasetId'] => $item];
            });


            foreach ($mystuff as $dataset => $value) {
                $newdata[$dataset] = collect($value)->mapToGroups(function ($item) {
                    return [$item['count_type'] => $item];

                });
            }

            $finaldata = [];

            foreach ($newdata[$request->dataset_id] as $counttype) {
                foreach ($counttype as $countdata) {
                    $finaldata[$countdata['count_type']][] = ['date' => Carbon::createFromDate($countdata['DatetimeStamp'])->toDayDateTimeString(), 'count' => $countdata['Value']];
                }
            }

            return $this->apiResponse(
                200,
                'Usage Data',
                [$request->dataset_id => $finaldata],
                'usage_data'
            );
        }

        return $this->apiResponse(
            418,
            "I'm not actually a teapot but no information was available from PSO",
            ['you asked for usage data' => ['for dataset' => $request->dataset_id, 'from' => $request->base_url]]
        );

    }

    public function sendPayloadToPSO($payload, $token, $base_url, $requires_pso_response = false): PromiseInterface|Response
    {
        $endpoint_segment = $requires_pso_response ? 'appointment' : 'data';

        return Http::timeout(5)
            ->withHeaders(['apiKey' => $token])
            ->connectTimeout(5)
            ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/' . $endpoint_segment, $payload);
    }

    public function processPayload($send_to_pso, $payload, $token, $base_url, $desc_200, $requires_rota_update = false, $dataset_id = null, $rota_id = null)
    {
        if ($send_to_pso) {

            $response = $this->sendPayloadToPSO($payload, $token, $base_url);

            if ($response->json('InternalId') > -1) {
                // update the rota
                if ($requires_rota_update) {
                    $this->sendRotaToDSE(
                        $dataset_id,
                        $rota_id,
                        $base_url,
                        null,
                        true
                    );
                }
                // send the good response
                return $this->apiResponse(200, ("Payload successfully sent to PSO." . ($desc_200 ? ' ' . $desc_200 : $desc_200)), $payload);
            } else {
                if ($response->serverError()) {
                    return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
                }

                if ($response->json('InternalId') == "-1") {
                    return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
                }

                if ($response->json('Code') == 401) {
                    return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
                }

                if ($response->status() == 500) {
                    return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
                }

                if ($response->status() == 401) {
                    return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
                }
            }
        }

        return $this->apiResponse(202, "Payload not sent to PSO", $payload);

    }
}
