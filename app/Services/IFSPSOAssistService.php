<?php

namespace App\Services;

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

    public function InputReferenceData($description, $dataset_id, $input_type, $datetime, $dse_duration = null, $process_type = null, $appointment_window = null)
    {


        $input_reference =
            [
                'datetime' => $datetime ?: Carbon::now()->toAtomString(),
                'id' => Str::orderedUuid()->getHex()->toString(),
                'description' => "$description",
                'input_type' => strtoupper($input_type),
                'organisation_id' => '2',
                'dataset_id' => $dataset_id,
            ];

        if ($dse_duration) {
            $input_reference = Arr::add($input_reference, 'duration', $dse_duration);
        }

        if ($process_type) {
            $input_reference = Arr::add($input_reference, 'process_type', strtoupper($process_type));
        }

        if ($appointment_window != null) {
            $input_reference = Arr::add($input_reference, 'appointment_window_duration', $appointment_window);
        }


        return $input_reference;

    }

    public function RotaToDSEPayload($dataset_id, $rota_id, $datetime = null): array
    {

        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData("Update Rota from the Thingy", $dataset_id, "CHANGE", $datetime ?: Carbon::now()->toAtomString()),
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter($rota_id ?: $dataset_id),
            ]
        ];
    }

    public function sendRotaToDSEPayload($dataset_id, $rota_id, $base_url, $date = null, $send_to_pso = null)
    {

        $payload = $this->RotaToDSEPayload($dataset_id, $rota_id, $date);
        if ($send_to_pso) {
            $rotatodse = Http::withHeaders(['apiKey' => $this->token])
                ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    $payload
                );

            if ($rotatodse->json('InternalId') == "-1") {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }
            if ($rotatodse->json('InternalId') != "-1") {
                return $this->apiResponse(200, "Payload sent to PSO", $payload);
            }

            // todo some more http error validation here

        } else {

            return response()->json([
                'status' => 202,
                'description' => 'not send to PSO',
                'original_payload' => [$payload]
            ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }

    }

    public function apiResponse($code, $description, $payload, $payload_desc = null): JsonResponse
    {
        // all other services will call this method for payloads
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
        $dse_duration = 'P' . $request->dse_duration . 'D';
        if ($request->appointment_window) {
            $appointment_window = 'P' . $request->appointment_window . 'D';
        } else {
            $appointment_window = null;
        }
        $process_type = $request->process_type ?: 'APPOINTMENT';
        $rota_id = $request->rota_id ?: $request->dataset_id;

        $input_ref = $this->InputReferenceData(
            $description,
            $request->dataset_id,
            "LOAD",
            $datetime,
            $dse_duration,
            $process_type,
            $appointment_window
        );

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

        if ($request->send_to_pso) {
            $rotatodse = Http::withHeaders(['apiKey' => $this->token])
                ->post($request->base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    $payload
                );

            if ($rotatodse->json('InternalId') == "-1") {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }
            if ($rotatodse->json('InternalId') != "-1") {
                return $this->apiResponse(200, "Payload sent to PSO", $payload);
            }

        } else {

            return response()->json([
                'status' => 202,
                'description' => 'not send to PSO',
                'original_payload' => [$payload]
            ], 202, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }


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


            $mystuff = collect($usage->collect()->first())->map(function ($item, $key) {

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
                $newdata[$dataset] = collect($value)->mapToGroups(function ($item, $key) {
                    return [$item['count_type'] => $item];

                });
            }

            $finaldata = [];

            foreach ($newdata[$request->dataset_id] as $counttype) {
                foreach ($counttype as $countdata) {
                    $finaldata[$countdata['count_type']][] = ['date' => $countdata['DatetimeStamp'], 'count' => $countdata['Value']];
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
            ['please give me usage data' => ['for dataset' => $request->dataset_id, 'from' => $request->base_url]]
        );

    }

    public function sendPayloadToPSO($payload, $token, $base_url): PromiseInterface|Response
    {
        return Http::timeout(5)
            ->withHeaders(['apiKey' => $token])
            ->connectTimeout(5)
            ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data', $payload);
    }
}
