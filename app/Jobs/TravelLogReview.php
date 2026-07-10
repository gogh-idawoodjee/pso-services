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
        Log::withContext(['travelLogId' => $this->travelLog->id, 'endpoint' => 'travelanalyzer.review']);
        Log::info('Running delayed TravelLogReview task', ['currentStatus' => $this->travelLog->status->value]);

        if ($this->travelLog->status !== TravelLogStatus::COMPLETED) {
            Log::info('Travel log not completed within timeout window; marking TIMEOUT');
            $this->travelLog->update(['status' => TravelLogStatus::TIMEOUT]);
        }

        Log::info('TravelLogReview task handled');
    }
}
