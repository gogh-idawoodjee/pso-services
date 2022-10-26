<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IFSPSOAssistService
{


    public function __construct()
    {
    }



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

    public function InputReferenceData($description, $dataset_id, $input_type)
    {
        return
            [
                'datetime' => Carbon::now()->toAtomString(),
                'id' => Str::orderedUuid()->getHex()->toString(),
                'description' => "$description",
                'input_type' => strtoupper($input_type),
                'organisation_id' => '2',
                'dataset_id' => $dataset_id,
            ];

    }

    public function RotaToDSEPayload($dataset_id, $rota_id)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData("Update Rota from the Thingy", $dataset_id, "CHANGE"),
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter($rota_id),
            ]
        ];
    }

    public function sendRotaToDSEPayload($dataset_id, $rota_id, $token, $base_url)
    {
        return Http::withHeaders(['apiKey' => $token])
            ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                $this->RotaToDSEPayload($dataset_id, $rota_id)
            );
//
//
//        $status = $sendRota->status();
//        $data = $sendRota->collect();
    }

    private function SourceData()
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
            ];
    }
}
