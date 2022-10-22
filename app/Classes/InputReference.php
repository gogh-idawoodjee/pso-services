<?php

namespace App\Classes;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class InputReference
{

    private string $description;
    private string $id_prefix;
    private string $type;
    private string $dataset_id;


    public function __construct($id_prefix, $description, $type, $dataset_id)
    {
        $this->id_prefix = $id_prefix;
        $this->description = $description;
        $this->type = $type;
        $this->dataset_id = $dataset_id;

        return $this;
    }


    public function InputReferenceJson(): array
    {
        return
            [
                'datetime' => Carbon::now()->toAtomString(),
                'id' => $this->id_prefix . '_' . Uuid::uuid4(),
                'description' => "$this->description",
                'input_type' => "$this->type",
                'organisation_id' => '2',
                'dataset_id' => $this->dataset_id,
                'duration' => 'P7D',
                'process_type' => 'APPOINTMENT',
                'appointment_window_duration' => 'P90D'
            ];

    }
}
