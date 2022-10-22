<?php

namespace App\Http\Livewire\Getcrazy;

use App\Services\IFSPSOResourceService;
use Livewire\Component;
use App\Services\IFSPSOGarabageService;

class PsoResource extends Component
{

    public $resources = [];

    public function mount()
    {
        $resources = new IFSPSOGarabageService(null,null,null,null,null,null,'cb847e5e-8747-4a02-9322-76530ef38a19');
        $this->resources = collect($resources->getScheduleableResources('W&C Prod','test'));
    }

    public function render()
    {

        return view('livewire.getcrazy.pso-resource');
    }
}
