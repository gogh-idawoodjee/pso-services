<?php

namespace App\Http\Livewire;

use App\Services\IFSPSOAssistService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Psousage extends Component
{
    public $usage_data;
    public $invalid_auth;
    public $usage_response;
    public $http_status;

    protected $rules = [

        'usage_data.base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
        'usage_data.username' => 'required',
        'usage_data.password' => 'required',
        'usage_data.dataset_id' => 'required',
        'usage_data.account_id' => 'required',
    ];

    protected $messages = [

        'usage_data.base_url.not_regex' => 'Cannot use a production URL.'

    ];

    /**
     * @throws ValidationException
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function getUsage()
    {
        $this->invalid_auth = false;
        $validatedData = $this->validate();

        $usage_init = new IFSPSOAssistService($this->usage_data['base_url'], $this->usage_data['token'], $this->usage_data['username'], $this->usage_data['password'], $this->usage_data['account_id'], true);

        if (!$usage_init->isAuthenticated()) {
            $this->invalid_auth = true;
        } else {
            $usage_data = $usage_init->getUsageData(new Request($this->usage_data));
            $this->http_status = json_decode($usage_data->content())->status;
            if ($this->http_status == 200) {
                $this->usage_response = json_encode(json_decode($usage_data->content())->usage_data[0], [JSON_PRETTY_PRINT,JSON_UNESCAPED_SLASHES]);
            }

            if ($this->http_status == 404) {
                $this->usage_response = json_encode(json_decode($usage_data->content())->original_payload[0], [JSON_PRETTY_PRINT,JSON_UNESCAPED_SLASHES]);
            }
        }

    }

    public function mount()
    {
        $this->usage_data = [
            'base_url' => '',
            'dataset_id' => '',
            'account_id' => '',
            'username' => '',
            'password' => '',
            'mindate' => Carbon::now()->format('Y-m-d'),
            'maxdate' => Carbon::now()->add(1, 'day')->format('Y-m-d'),
            'token' => null
        ];
    }


    public function render()
    {
        return view('livewire.psousage');
    }
}
