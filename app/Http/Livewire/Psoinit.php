<?php

namespace App\Http\Livewire;

use App\Models\PsoEnvironment;
use App\Services\IFSPSOAssistService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Livewire\Component;

class Psoinit extends Component
{

    public $init_data;
    public $http_status = null;
    public $description;
    public $original_payload;
    public $environments;
    public $datasets;
    public $dataset;
    public $environment;

    protected $rules = [
        'init_data.account_id' => 'required_if:send_to_pso,true',
        'init_data.base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
        'init_data.process_type' => 'required'
    ];

    protected $messages = [

        'init_data.base_url.not_regex' => 'Cannot use a production URL.'

    ];

    /**
     * @throws ValidationException
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        if ($propertyName == 'environment') {
            $env = $this->environments->where('id', $this->environment)->first();
            $this->init_data['base_url'] = $env->base_url;
            $this->init_data['username'] = $env->username;
            $this->init_data['password'] = $env->password;
            $this->init_data['account_id'] = $env->account_id;

            // get the datasets
            $this->datasets = $env->datasets;
        }

        if ($propertyName == 'dataset') {
            $dataset = $this->datasets->where('id', $this->dataset)->first();
            $this->init_data['dataset_id'] = $dataset->dataset_id;
            $this->init_data['rota_id'] = $dataset->rota_id;
        }
    }

    public function initPSO()
    {
        $this->reset_fields();

        $this->withValidator(function (Validator $validator) {

            $validator->after(function ($validator) {

                if ($this->init_data['send_to_pso'] && !$this->init_data['token'] && !$this->init_data['username'] && !$this->init_data['password']) {
                    $validator->errors()->add('init_data.token', 'Please supply a user/pass if not supplying a token');
                }
            });

        })->validate();

        $validatedData = $this->validate();

        $init = new IFSPSOAssistService($this->init_data['base_url'], $this->init_data['token'], $this->init_data['username'], $this->init_data['password'], $this->init_data['account_id'], $this->init_data['send_to_pso']);

        if (!$init->isAuthenticated() && $this->init_data['send_to_pso']) {

            $this->reset_fields();
            $this->http_status = 401;
        } else {


            $response = $init->InitializePSO(new Request($this->init_data));
            $this->http_status = json_decode($response->content())->status;
            $this->description = json_decode($response->content())->description;
            $this->original_payload = json_encode(json_decode($response->content())->original_payload[0], JSON_PRETTY_PRINT);
        }

    }

    private function reset_fields()
    {
        $this->http_status = null;
        $this->description = null;
        $this->original_payload = null;
    }

    public function mount()
    {

        $this->init_data = [
            'description' => 'init from the thingy',
            'send_to_pso' => false,
            'base_url' => '',
            'dataset_id' => '',
            'rota_id' => '',
            'account_id' => '',
            'username' => '',
            'password' => '',
            'dse_duration' => 7,
            'appointment_window' => 14,
            'datetime' => Carbon::create(now())->format("Y-m-d\TH:i"),
            'token' => null,
            'process_type' => 'APPOINTMENT'
        ];

        $this->environments = PsoEnvironment::where('user_id', '=', Auth::user()->id)->with('datasets', 'defaultdataset')->get();

    }

    public function render()
    {
        return view('livewire.psoinit');
    }
}
