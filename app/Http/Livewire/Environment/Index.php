<?php

namespace App\Http\Livewire\Environment;

use App\Models\PsoDataset;
use App\Models\PsoEnvironment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{

    public $environments;
    public $env_data;

    protected $rules = [
        'env_data.account_id' => 'required',
        'env_data.name' => 'required', //
        'env_data.base_url' => ['url', 'required', 'not_regex:/prod|prd/i'], //
        'env_data.username' => 'string|required', //
        'env_data.password' => 'string', //
        'env_data.dataset_id' => 'string|required', //
        'env_data.rota_id' => 'string' //
    ];

    protected $messages = [

        'env_data.base_url.not_regex' => 'Cannot use a production URL.'

    ];

    /**
     * @throws ValidationException
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

    }

    public function mount()
    {
        $this->environments = $this->getUserEnvironments();
    }


    public function addEnvironment()
    {
        $validatedData = $this->validate();

        $environment = new PsoEnvironment();
        $environment->user_id = Auth::user()->id;
        $environment->name = $this->env_data['name'];
        $environment->base_url = $this->env_data['base_url'];
        $environment->account_id = $this->env_data['account_id'];
        $environment->username = $this->env_data['username'];
        $environment->password = $this->env_data['password']; // check if exists
        $environment->save();

        $dataset = new PsoDataset();
        $dataset->dataset_id = $this->env_data['dataset_id'];
        $dataset->rota_id = Arr::exists($this->env_data, 'rota_id') ? $this->env_data['rota_id'] : $this->env_data['dataset_id'];
        $dataset->user_id = Auth::user()->id;
        $dataset->environment()->associate($environment);
        $dataset->save();

        $this->environments = collect($this->environments)->push($environment);

    }

    public function deleteEnvironment($id)
    {
        $env = PsoEnvironment::destroy($id);
        $this->environments = $this->getUserEnvironments();
    }

    private function getUserEnvironments()
    {
        return PsoEnvironment::where('user_id', '=', Auth::user()->id)->with('datasets', 'defaultdataset')->get();
    }

    public function render()
    {
        return view('livewire.environment.index');
    }
}
