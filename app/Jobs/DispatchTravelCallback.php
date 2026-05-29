<?php

namespace App\Jobs;

use App\Enums\TravelLogStatus;
use App\Models\V2\PSOTravelLog;
use App\Services\V2\TravelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchTravelCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 30, 120];

    public function __construct(public PSOTravelLog $travelLog) {}

    public function handle(TravelService $travelService): void
    {
        if ($this->travelLog->status !== TravelLogStatus::COMPLETED) {
            Log::info("Travel callback skipped — log {$this->travelLog->id} not completed");
            return;
        }

        $results = $travelService->getTravelResults($this->travelLog);

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->post($this->travelLog->callback_url, [
                'travelLogId' => $this->travelLog->id,
                'status' => 'completed',
                'results' => $results,
            ]);

        if ($response->failed()) {
            Log::warning("Travel callback failed for {$this->travelLog->id}", [
                'url' => $this->travelLog->callback_url,
                'status' => $response->status(),
            ]);

            $this->release($this->backoff[$this->attempts() - 1] ?? 120);
            return;
        }

        Log::info("Travel callback delivered for {$this->travelLog->id}");
    }
}
