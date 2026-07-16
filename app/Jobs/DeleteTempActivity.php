<?php

namespace App\Jobs;


use App\Classes\AuthenticatedPsoActionService;
use App\DataTransferObjects\PsoContext;
use App\Models\V2\PSOAppointment;
use App\Services\V2\DeleteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DeleteTempActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            // This job runs outside the HTTP request lifecycle (queue worker, no Request object),
            // so it can't reuse PSOAssistV2's request-based auth resolution. Credentials were
            // captured at appointment-creation time and encrypted into service_api_input
            // (see AppointmentService::encryptSensitiveEnvironmentFields) specifically so this
            // delayed cleanup job can re-authenticate on its own later.
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
                function (array $auth) use ($deletePayloadToServicesApi) {
                    $deletePayloadToServicesApi['environment']['token'] = data_get($auth, 'token');
                    $context = new PsoContext(
                        token: data_get($auth, 'token'),
                        validated: $deletePayloadToServicesApi,
                    );

                    return app(DeleteService::class)->deleteObject($context);
                }
            );

            // update the record
            $this->appointment->update(['cleanup_datetime' => now(), 'required_manual_cleanup' => true]);


            Log::info("PSO Appointment Temp Activity: {$this->appointment->activity_id} offers have expired, not been actioned and is being deleted");

        }

        Log::info("PSO Appointment Temp Activity Delete: {$this->appointment->appointment_request_id} handled");
    }
}
