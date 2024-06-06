<?php

namespace App\Jobs;

use App\Services\PSOLoadTestService;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class BookAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */


    private $loop_count;
    private $task_prefix;
    private $data_lat;
    private $data_long;
    private $data_durations;
    private $data_activity_types;
    private $run_id;
    private $check_appointed;
    private $dataset_id;
    private $relative_start;

    public function __construct(
        $loop_count,
        $task_prefix,
        $data_lat,
        $data_long,
        $data_durations,
        $data_activity_types,
        $run_id,
        $dataset_id,
        $relative_start,
        $check_appointed
    )
    {
        //
        $this->loop_count = $loop_count;
        $this->task_prefix = $task_prefix;
        $this->data_lat = $data_lat;
        $this->data_long = $data_long;
        $this->data_durations = $data_durations;
        $this->data_activity_types = $data_activity_types;
        $this->run_id = $run_id;
        $this->dataset_id = $dataset_id;
        $this->relative_start = $relative_start;
        $this->check_appointed = $check_appointed;


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $loadtest = new PSOLoadTestService();
        $loadtest->setupLoadTest(
            $this->loop_count,
            $this->task_prefix,
            $this->data_lat,
            $this->data_long,
            $this->data_durations,
            $this->data_activity_types,
            $this->run_id,
            $this->dataset_id,
            $this->relative_start,
            $this->check_appointed
        );
        $loadtest->performLoadTest();


    }


}
