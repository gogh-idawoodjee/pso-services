<?php

namespace App\Http\Livewire\Getcrazy;

use app\Services\IFSPSOScheduleService;
use Livewire\Component;
use App\Models\PsoEnvironment;


class PsoSchedule extends Component
{

    public $environments;
    public $datasets = null;
    public $selectedEnvironment = null;
    public $selectedDataset = null;
    public $scheduleOutput;


    public function mount()
    {
        $this->scheduleOutput=[];
        $this->environments = PsoEnvironment::with('datasets')->get();
    }

    public function updateDatasets()
    {

        if (!is_null($this->selectedEnvironment) && $this->selectedEnvironment != "") {
            $this->datasets = $this->environments->firstWhere('id', $this->selectedEnvironment)->datasets;
        } else {
            $this->selectedDataset = null;
        }
    }

    public function getSchedule()
    {
        $schedule = new IFSPSOScheduleService($this->selectedEnvironment);
        $this->scheduleOutput = ($schedule->getSchedule($this->selectedDataset)->collect());
    }

    function render()
    {
        return view('livewire.getcrazy.pso-schedule');
    }
}
