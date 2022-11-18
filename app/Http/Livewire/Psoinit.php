<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Psoinit extends Component
{

    public bool $send_to_pso = false;
    public $base_url;
    public $dataset_id;
    public $rota_id;
    public $account_id;
    public $username;
    public $password;
    public $dse_duration;
    public $appointment_window;
    public $process_type;
    public $description;
    public $datetime;


    public function render()
    {
        return view('livewire.psoinit');
    }
}
