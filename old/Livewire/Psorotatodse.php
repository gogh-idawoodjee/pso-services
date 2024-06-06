<?php

namespace old\Livewire;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Livewire\Component;

class Psorotatodse extends Component
{

    public $rota_data;
    public $http_status = null;

    protected $rules = [
        'rota_data.account_id' => 'required_if:send_to_pso,true',
        'rota_data.base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i']
    ];

    protected $messages = [

        'rota_data.base_url.not_regex' => 'Cannot use a production URL.'

    ];

    /**
     * @throws ValidationException
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function rotaToDSE()
    {
        $this->withValidator(function (Validator $validator) {

            $validator->after(function ($validator) {

                if ($this->rota_data['send_to_pso'] && !$this->rota_data['token'] && !$this->rota_data['username'] && !$this->rota_data['password']) {
                    $validator->errors()->add('rota_data.token', 'Please supply a user/pass if not supplying a token');
                }
            });

        })->validate();

        $validatedData = $this->validate();
    }

    public function mount()
    {
        $this->rota_data = [
            'description' => 'rota update from the array',
            'send_to_pso' => false,
            'base_url' => '',
            'dataset_id' => '',
            'rota_id' => '',
            'account_id' => '',
            'username' => '',
            'password' => '',
            'datetime' => Carbon::create(now())->format("Y-m-d\TH:i"),
            'token' => null
        ];
    }

    public function render()
    {
        return view('livewire.psorotatodse');
    }
}
