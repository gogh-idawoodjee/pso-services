<?php

namespace App\Http\Controllers;

use App\Models\PsoDataset;
use App\Models\PsoEnvironment;
use App\Rules\NoProdURL;
use app\Services\Rota\PSOAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RotaAPIController extends Controller
{
    //
    public function sendRotaAuto(Request $request)
    {
        clock()->info('starting');

        $validator = Validator::make($request->all(), [
            'push_dataset_id' => 'required',
            'pso_environment_id' => 'required',
            'input_type' => 'required',
        ]);

        if ($validator->fails()) {
            clock()->info('it failed');
            $validator->errors()->add('doobas', 'deebas');
            $validator->after(function ($validator) use ($request) {

                $validator->errors()->add('index_row', $request->pso_environment_id);

            });
        }
//
        $validator->validate();


        clock()->info('made it to sendRotaAuto');
        // find the dataset from here
        $dataset = PsoDataset::firstWhere('id', $request->push_dataset_id)->get();
        // grab the environment
        $environment = PsoEnvironment::firstWhere('id', $request->pso_environment_id)->get();
        // grab the token
//        $token = $environment->token()->first()->token;
        // check if it's valid???
        // send that shizz??

        return $request;

    }


    private function authenticatePSO($user, $pass, $account_id, $url): PSOAuthService
    {
        return PSOAuthService::authenticate($user, $pass, $account_id, $url);
    }

    public function sendRotaManual(Request $request)
    {
        $request->base_url = trim($this->remove_http($request->base_url));
        $request->username = trim($request->username);
        $request->password = trim($request->password);
        $request->account_id = trim($request->account_id);
        $request->dataset_id = trim($request->dataset_id);
        $request->rota_id = trim($request->rota_id);


        $request->validate([
            'base_url' => ['required', new NoProdURL],
            'username' => 'required',
            'password' => 'required',
            'account_id' => 'required|alpha_num',
            'dataset_id' => 'required',
            'rota_id' => 'required',
        ]);

        // authenticate first and return if issues
        $key = $this->authenticatePSO($request->username, $request->password, $request->account_id, $request->base_url);
        if ($key->isAuthenticated()) {
            clock()->info('isauth');

            $sendRota = Http::withHeaders(['apiKey' => $key->getToken()])
                ->post('https://' . $request->base_url . config('rota.pso.pso_data_endpoint_path'),
                    $this->RotaPayload($request->input_type == 'change' ? 'Update Rota from the Thingy' : 'Init from the Thingy',
                        $request->dataset_id,
                        $request->rota_id,
                        $request->input_type)
                );
            $status = $sendRota->status();
            $data = $sendRota->collect();

        } else {
            clock()->info('is not auth');

            $status = $key->getStatus();
            $data = null;
        }
        return [
            'rota_response' => [
                'data' => $data,
                'errors' => $key->getErrors(),
                'status' => $status
            ]
        ];


    }

    private function InputReferenceData($description, $dataset_id, $input_type)
    {
        return
            [
                'datetime' => Carbon::now()->toAtomString(),
                'id' => Str::orderedUuid()->getHex()->toString(),
                'description' => "$description",
                'input_type' => strtoupper($input_type),
                'organisation_id' => '2',
                'dataset_id' => $dataset_id,
                'schedule_data' => 'CONTINUOUS',
                'load_status' => '0',
                'duration' => 'P60D',
                'process_type' => 'APPOINTMENT',
            ];

    }

    private function SourceData()
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
            ];
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

    private function RotaPayload($description, $dataset_id, $rota_id, $input_type)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData($description, $dataset_id, $input_type),
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter($rota_id),

            ]
        ];
    }

    private function remove_http($url)
    {
        $disallowed = array('http://', 'https://');
        foreach ($disallowed as $d) {
            if (str_starts_with($url, $d)) {
                return str_replace($d, '', $url);
            }
        }
        return $url;
    }

    public function getEnvrionments()
    {
        return PsoEnvironment::with('datasets')->get();
    }

}
