# Travel Analyzer Callback Implementation Plan

## Context

The PSO Services API (`pso-services`) now supports an optional `data.callbackUrl` on the `POST /api/v2/travelanalyzer` endpoint. When provided, results are POSTed to that URL automatically once PSO broadcasts back the travel analysis — no polling needed.

The Filament UI needs to be updated to use this callback mechanism instead of (or in addition to) showing a modal with the raw initial response.

## How it works

### Current flow (polling / manual)
1. User fills in coords, clicks "Analyze Travel"
2. `analyzeTravel()` sends POST to `pso-services`
3. Response is the initial payload echo + a `resultsUrl` to poll
4. User sees the raw response in a modal — results aren't there yet
5. User would have to manually hit the GET endpoint to see actual results

### New flow (callback)
1. User fills in coords, clicks "Analyze Travel"
2. UI sends POST to `pso-services` with `data.callbackUrl` pointing to a route in the Filament app
3. `pso-services` forwards to PSO, returns immediately with travel log ID
4. UI shows a "waiting for results" state (spinner, pending indicator, etc.)
5. When PSO responds (seconds to ~1 min), `pso-services` POSTs results to the callback URL
6. The callback route receives and stores the results
7. UI updates to show the results (via polling the local DB, or Livewire polling, or broadcasting)

## Implementation Steps

### 1. Create a callback route and controller

Create a route that `pso-services` can POST results to:

```php
// routes/web.php or routes/api.php
Route::post('/api/travel-callback', [TravelCallbackController::class, 'receive'])
    ->name('travel.callback');
```

The controller receives this payload from pso-services:

```json
{
  "travelLogId": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "status": "completed",
  "results": {
    "travel_detail_request_id": "a1b2c3d4-...",
    "start_address": "123 Queen St W, Toronto, ON",
    "end_address": "456 King St E, Toronto, ON",
    "pso": {
      "time": "00:25:00",
      "distance": "12.5 km"
    },
    "google": {
      "time": "22 mins",
      "distance": "11.8 km"
    }
  }
}
```

The controller should:
- Validate the payload (travelLogId, status, results)
- Store/cache the results keyed by `travelLogId` (DB table, cache store, or model — whatever fits)
- Optionally broadcast a Livewire event or Laravel event so the UI can react in real-time

### 2. Update `analyzeTravel()` to include the callback URL

In `TravelAnalyzer.php`, update the payload to include the callback:

```php
$payload = array_merge(
    $this->environment_payload_data(),
    [
        'data' => [
            'latTo' => $get('lat_to'),
            'latFrom' => $get('lat_from'),
            'longFrom' => $get('long_from'),
            'longTo' => $get('long_to'),
            'sendToPso' => $this->environment_data['send_to_pso'],
            'googleApiKey' => config('psott.google_api_key'),
            'callbackUrl' => route('travel.callback'),
        ],
    ]
);
```

### 3. Track the pending travel log ID

After sending the request, extract and store the travel log ID so the UI knows what to look for:

```php
// After sending, extract the travel log ID from the response
$responseData = $this->response; // however you access the JSON
$this->travelLogId = data_get($responseData, 'data.payloadToPso.dsScheduleData.Travel_Detail_Request.0.id');
```

Add `public ?string $travelLogId = null;` as a property on the page.

### 4. Update the UI to show a waiting/results state

Instead of immediately showing the raw JSON modal, show a results section:

**Option A: Livewire polling (simplest)**
```php
// In the Blade view, poll every 3 seconds while waiting
@if($travelLogId && !$travelResults)
    <div wire:poll.3s="checkResults">
        Waiting for PSO results...
    </div>
@endif

@if($travelResults)
    {{-- Show formatted results: PSO time/distance, Google time/distance, addresses --}}
@endif
```

```php
// On the page class
public ?array $travelResults = null;

public function checkResults(): void
{
    if (!$this->travelLogId) return;

    // Check whatever store you used in step 1
    $result = TravelResult::find($this->travelLogId);
    // or: $result = Cache::get("travel-result:{$this->travelLogId}");

    if ($result) {
        $this->travelResults = $result->results; // or however it's shaped
        $this->travelLogId = null; // stop polling
    }
}
```

**Option B: Laravel Echo / broadcasting (real-time, no polling)**
- In the callback controller, broadcast an event: `TravelResultReceived::dispatch($travelLogId, $results)`
- On the Filament page, listen via Echo: `$this->dispatch('travel-result-received')`
- This is fancier but requires a broadcast driver (Pusher, Reverb, etc.)

Option A is totally fine for this use case since results come back in seconds.

### 5. Display the results nicely

Once results arrive, show them in a structured way instead of raw JSON:

| Field | Source | Value |
|-------|--------|-------|
| From | Geocoder | 123 Queen St W, Toronto, ON |
| To | Geocoder | 456 King St E, Toronto, ON |
| Travel Time | PSO | 25 minutes |
| Distance | PSO | 12.5 km |
| Travel Time | Google | 22 mins |
| Distance | Google | 11.8 km |

You could use a Filament `Section` with `ViewEntry` or `TextEntry` components, or a simple Blade partial.

### 6. Keep the GET endpoint as fallback

The raw JSON modal / `resultsUrl` can still be shown as a secondary option (e.g., a "View Raw Response" button) for debugging or if the callback fails.

## Important Notes

- The callback URL must be accessible from wherever `pso-services` runs. If both are local (Herd), `localhost` routes work fine. In production, make sure the URL is reachable.
- `pso-services` retries the callback 3 times with backoff (5s, 30s, 2min) if your endpoint fails.
- The callback route should NOT require authentication (it's a server-to-server call from pso-services). Consider validating using the `travelLogId` — only accept callbacks for IDs your app actually requested.
- If `sendToPso` is false (dry run), there's no callback — PSO never gets the request so there's nothing to broadcast back.
