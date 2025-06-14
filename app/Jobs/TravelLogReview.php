<?php

namespace App\Jobs;

use App\Enums\TravelLogStatus;
use App\Models\V2\PSOTravelLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TravelLogReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PSOTravelLog $travelLog)
    {
    }

    public function handle(): void
    {
        Log::info("Running delayed task for travelLogId: {$this->travelLog->id}");

        if ($this->travelLog->status !== TravelLogStatus::COMPLETED) {
            Log::info("travelLogId: {$this->travelLog->id} is not completed, updating status to TIMEOUT");
            $this->travelLog->update(['status' => TravelLogStatus::TIMEOUT]);
        }

        Log::info("travelLogId: {$this->travelLog->id} handled");
    }
}
