<?php /** @noinspection PhpUndefinedFieldInspection */

namespace App\Http\Livewire\Environment;

use App\Models\PsoDataset;
use App\Models\PsoEnvironment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{

    public $env_data;
    public $dataset_data;
    public $datasets = [];
    public $environment;

    protected $rules = [
        'env_data.account_id' => 'required',
        'env_data.name' => 'required', //
        'env_data.base_url' => ['url', 'required', 'not_regex:/prod|prd/i'], //
        'env_data.username' => 'string|required', //
        'env_data.password' => 'string', //

    ];

    private $dataset_rules = [
        'dataset_data.dataset_id' => 'string|required', //
        'dataset_data.rota_id' => 'string' //
    ];

    protected $messages = [

        'env_data.base_url.not_regex' => 'Cannot use a production URL.'

    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

    }


    public function updateEnvironment()
    {
        $validatedData = $this->validate($this->dataset_rules);

        $this->environment->name = $this->env_data['name'];
        $this->environment->base_url = $this->env_data['base_url'];
        $this->environment->account_id = $this->env_data['account_id'];
        $this->environment->username = $this->env_data['username'];
        $this->environment->password = $this->env_data['password']; // check if exists
        $this->environment->save();

    }

    public function deleteDataset($id)
    {
        $dataset = PsoDataset::destroy($id);
        $this->refreshDataFromServer();
    }

    public function addDataset()
    {
        $validatedData = $this->validate();

        $dataset = new PsoDataset();
        $dataset->dataset_id = $this->dataset_data['dataset_id'];
        $dataset->rota_id = Arr::exists($this->dataset_data, 'rota_id') ? $this->dataset_data['rota_id'] : $this->dataset_data['dataset_id'];
        $dataset->user_id = Auth::user()->id;
        $dataset->environment()->associate($this->environment);
        $dataset->save();

        $this->refreshDataFromServer();


    }


    public function mount($id)
    {
        $this->environment = $this->getEnvironment($id);
        $this->datasets = $this->environment->datasets;
        $this->env_data['name'] = $this->environment->name;
        $this->env_data['username'] = $this->environment->username;
        $this->env_data['password'] = $this->environment->password;
        $this->env_data['base_url'] = $this->environment->base_url;
        $this->env_data['account_id'] = $this->environment->account_id;

    }

    private function refreshDataFromServer()
    {
        $this->environment = $this->getEnvironment($this->environment->id);
        $this->datasets = $this->environment->datasets;
    }


    private function getEnvironment($id)
    {
        return PsoEnvironment::where('user_id', '=', Auth::user()->id)
            ->where('id', '=', $id)
            ->with('datasets', 'defaultdataset')
            ->first();
    }

    public function render()
    {
        return view('livewire.environment.edit');
    }
}
