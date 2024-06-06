<?php

namespace old\Livewire\Getcrazy;

use App\Services\IFSPSOResourceService;
use Livewire\Component;

class PsoResourceShow extends Component
{

    private string $resource_id;
    public $resource;
    public $utilization;
    public $events;
    public $locations;
    public $shifts;


    public function mount($resource_id)
    {
        $this->resource_id = $resource_id;
        $resource = new IFSPSOResourceService(null,null,null,null,null,null,'cb847e5e-8747-4a02-9322-76530ef38a19');
        $this->resource = $resource->getResource($this->resource_id, 'W&C Prod','thetechnodro.me:950');
        $this->utilization = $resource->getResourceUtilization();
        $this->events = $resource->getResourceEvents();
        $this->locations = $resource->getResourceLocations();
        $this->shifts = $resource->getResourceShiftsFormatted();

    }

    public function render()
    {

        return view('livewire.getcrazy.pso-resource-show');
    }
}
