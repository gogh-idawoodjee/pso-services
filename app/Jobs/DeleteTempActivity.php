<?php

namespace App\Jobs;


use App\Classes\AuthenticatedPsoActionService;
use App\Models\V2\PSOAppointment;
use App\Services\V2\DeleteService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DeleteTempActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PSOAssistV2;

    public function __construct(public PSOAppointment $appointment)
    {
        Log::info("delete has been called");
    }

    public function handle(): void
    {
        Log::info("Running delayed task for PSO Appointment Temp Activity Delete: {$this->appointment->appointment_request_id}");
        Log::info('cleanupdatetime: ' . $this->appointment->cleanup_datetime);
        Log::info('is in the past: ' . $this->appointment->offer_expiry_datetime->isPast());

        if (!$this->appointment->cleanup_datetime && $this->appointment->offer_expiry_datetime->isPast()) {
            // Delete the activity
            // problem bruv, we don't have creds to do deletions here
            // we could encrypt and store them
            // but the services API could've received a token, pwnd  I suppose that's an edge case? store the token as well and try it?
            $service_api_input = $this->appointment->service_api_input;

            $environment = [
                'baseUrl' => data_get($service_api_input, 'baseUrl'),
                'datasetId' => data_get($service_api_input, 'datasetId'),
                'sendToPso' => true,
                'accountId' => data_get($service_api_input, 'accountId'),
            ];

            // Conditionally add authentication credentials
            if (data_get($service_api_input, 'username') && data_get($service_api_input, 'password')) {
                $environment['username'] = Crypt::decryptString(data_get($service_api_input, 'username'));
                $environment['password'] = Crypt::decryptString(data_get($service_api_input, 'password'));
            } elseif (data_get($service_api_input, 'token')) {
                $environment['token'] = Crypt::decryptString(data_get($service_api_input, 'token'));
            }

            $deletePayloadToServicesApi = [
                'environment' => $environment,
                'data' => [
                    'objectType' => 'Activity',
                    'id' => $this->appointment->activity_id,
                ],
            ];

            $psoAuth = app(AuthenticatedPsoActionService::class);

            $response = $psoAuth->run(
                data_get($deletePayloadToServicesApi, 'environment'),
                function (string|null $token) use ($deletePayloadToServicesApi) {
                    // This is where you do what the controller would do after getting a token
                    return (new DeleteService($token, $deletePayloadToServicesApi))->deleteObject(); // return JsonResponse
                }
            );

            // update the record
            $this->appointment->update(['cleanup_datetime' => now(), 'required_manual_cleanup' => true]);


            Log::info("PSO Appointment Temp Activity: {$this->appointment->activity_id} offers have expired, not been actioned and is being deleted");

        }

        Log::info("PSO Appointment Temp Activity Delete: {$this->appointment->appointment_request_id} handled");
    }
}
