<?php

namespace App\Jobs;

use App\Enums\TravelLogStatus;
use App\Models\V2\PSOTravelLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TravelLogReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $travelLogId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Running delayed task for travelLogId: {$this->travelLogId}");

        // Example: update the log again, or notify someone
        $log = PSOTravelLog::find($this->travelLogId);
        if ($log->status !== TravelLogStatus::COMPLETED) {
            Log::info("travelLogId: {$this->travelLogId} is not completed, updating status to TIMEOUT");
            $log->update(['status' => TravelLogStatus::TIMEOUT]);
        }
        Log::info("travelLogId: {$this->travelLogId} handled");
    }
}
