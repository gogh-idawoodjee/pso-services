<?php

namespace old\Livewire\Resource;

use App\Models\PsoEnvironment;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{

    public $usage_data;
    public $invalid_auth;
    public $usage_response;
    public $http_status;

    public $environments;
    public $datasets;
    public $dataset;
    public $environment;
    public $resources=[];


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
        if ($propertyName == 'environment') {
            $env = $this->environments->where('id', $this->environment)->first();
            $this->usage_data['base_url'] = $env->base_url;
            $this->usage_data['username'] = $env->username;
            $this->usage_data['password'] = $env->password;
            $this->usage_data['account_id'] = $env->account_id;

            // get the datasets
            $this->datasets = $env->datasets;
        }

        if ($propertyName == 'dataset') {
            if ($this->dataset != 'no_dataset') {
                $dataset = $this->datasets->where('id', $this->dataset)->first();
                $env = $this->environments->where('id', $this->environment)->first();
                $this->usage_data['dataset_id'] = $dataset->dataset_id;
                $this->usage_data['rota_id'] = $dataset->rota_id;
                $this->usage_data['base_url'] = $env->base_url;
                $this->usage_data['username'] = $env->username;
                $this->usage_data['password'] = $env->password;
                $this->usage_data['account_id'] = $env->account_id;
            } else {
                $this->reset_fields();
                $this->clear_fields();
            }
        }
    }

    private function reset_fields()
    {
        $this->http_status = null;

    }

    private function clear_fields()
    {
        $this->usage_data['base_url'] = null;
        $this->usage_data['username'] = null;
        $this->usage_data['password'] = null;
        $this->usage_data['account_id'] = null;
        $this->usage_data['dataset_id'] = null;
    }


    public function getResources()
    {
        $this->invalid_auth = false;
        $this->usage_data['send_to_pso'] = true;
        $validatedData = $this->validate();

//        $usage_init = new IFSPSOAssistService($this->usage_data['base_url'], $this->usage_data['token'], $this->usage_data['username'], $this->usage_data['password'], $this->usage_data['account_id'], true);
        $resource_init = new IFSPSOResourceService($this->usage_data['base_url'], null, $this->usage_data['username'], $this->usage_data['password'], $this->usage_data['account_id'], true);

        if (!$resource_init->isAuthenticated()) {
            $this->invalid_auth = true;
        } else {
            $usage_data = $resource_init->getScheduleableResources(new Request($this->usage_data));
            $this->http_status = json_decode($usage_data->content())->status;
            if ($this->http_status == 200) {
                $this->usage_response = json_encode(json_decode($usage_data->content())->original_payload[0], JSON_PRETTY_PRINT);
                $this->resources = json_decode($usage_data->content())->original_payload[0];
            }

            if ($this->http_status == 404) {
                $this->usage_response = json_encode(json_decode($usage_data->content()), JSON_PRETTY_PRINT);
            }
        }

    }


    public function render()
    {
        return view('livewire.resource.index');
    }

    public function mount()
    {

        $this->environments = PsoEnvironment::where('user_id', '=', Auth::user()->id)->with('datasets', 'defaultdataset')->get();

    }

    // display envrionments list with get resources button
    // get resources
    // link to shifts
    // link to unavailablities
    // link to resource events
}
