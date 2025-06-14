<?php

namespace App\Jobs;


use App\Http\Requests\Api\V2\DeleteObjectRequest;
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
    }

    public function handle(): void
    {
        Log::info("Running delayed task for PSO Appointment Temp Activity Delete: {$this->appointment->appointment_request_id}");

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

            $response = $this->executeAuthenticatedAction($request, function (DeleteObjectRequest $req) {
                // so we have the token now in $req->input('environment.token')
                // we should send that the activity service? // all our services should accept a token
                $deleteService = new DeleteService(
                    $req->filled('environment.token') ? $req->input('environment.token') : null,
                    $req->validated()
                );

                return $deleteService->deleteObject();
            });

            Log::info("PSO Appointment Temp Activity: {$this->appointment->activity_id} offers have expired, not been actioned and is being deleted");

        }

        Log::info("PSO Appointment Temp Activity Delete: {$this->appointment->appointment_request_id} handled");
    }
}
