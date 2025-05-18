<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class InputReference
{

    private string $description;
    private string|null $datetime;
    private string $input_type;
    private string $dataset_id;
    private string|null $process_type;
    private string|null $dse_duration;
    private string|null $appointment_window;


    // nolonger on the to do list maybe this shouldn't be a class but a public method on the assist service // done
    public function __construct($description, $input_type, $dataset_id, $datetime = null, $dse_duration = null, $process_type = null, $appointment_window = null)
    {

        $this->description = $description;
        $this->input_type = $input_type;
        $this->dataset_id = $dataset_id;
        $this->datetime = $datetime;
        $this->process_type = $process_type;
        $this->appointment_window = $appointment_window;
        $this->dse_duration = $dse_duration;

        return $this;
    }


    public function toJson($id = null): array
    {

        $input_reference =
            [
                'datetime' => $this->datetime ?: Carbon::now()->toAtomString(),
                'id' => $id ?: Str::orderedUuid()->getHex()->toString(),
                'description' => $this->description,
                'input_type' => strtoupper($this->input_type),
                'organisation_id' => '2',
                'dataset_id' => $this->dataset_id,
                'user_id' => config('pso-services.settings.service_name')
            ];

        if ($this->dse_duration) {
            $input_reference = Arr::add($input_reference, 'duration', $this->dse_duration);
        }

        if ($this->process_type) {
            $input_reference = Arr::add($input_reference, 'process_type', strtoupper($this->process_type));
        }

        if ($this->appointment_window !== null) {
            $input_reference = Arr::add($input_reference, 'appointment_window_duration', $this->appointment_window);
        }

        return $input_reference;

    }
}
