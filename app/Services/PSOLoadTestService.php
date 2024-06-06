<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PSOLoadTestService
{

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
    private $appointment_request_id;

    public function setupLoadTest(
        $loop_count,
        $task_prefix,
        $data_lat,
        $data_long,
        $data_durations,
        $data_activity_types,
        $run_id,
        $dataset_id,
        $relative_start,
        $check_appointed)
    {
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
     * @throws ConnectionException
     */
    public function performLoadTest()
    {


        $noappointment_count = 0;

        for ($i = 0; $i < $this->loop_count; $i++) {


            $activity_id = $this->task_prefix . $i;
            $randomlatlong = array_rand($this->data_lat);
            $lat = $this->data_lat[$randomlatlong];
            $long = $this->data_long[$randomlatlong];
            $duration = array_rand($this->data_durations);
            $activity_type = array_rand($this->data_activity_types);


            $appointments = $this->rltGetAppointment(
                $activity_id,
                $this->data_activity_types[$activity_type],
                $this->data_durations[$duration],
                $lat,
                $long,
                $this->run_id,
                $this->dataset_id,
                $this->relative_start
            );


            $appointment_request_id = $this->setAppointmentRequestID($appointments);

            if ($this->hasOffers($appointments)) {
                $noappointment_count = 0;


                if ($this->check_appointed) {
                    $check_appointment = $this->checkAppointed($appointment_request_id, $this->bestOffer($appointments)['id'], $this->dataset_id);
                    if ($this->offerIsAvailable($check_appointment)) {

                        $accept = $this->acceptAppointment($appointment_request_id, $this->bestOffer($appointments)['id'], $this->dataset_id);
                    }
                } else {
                    $accept = $this->acceptAppointment($appointment_request_id, $this->bestOffer($appointments)['id'], $this->dataset_id);
                }


            } else {

                // appointments not returned
                $noappointment_count++;
                $this->declineAppointment($appointment_request_id);


                // set appointment template datetime +7 days if we have 3 no slots found in a row
                $this->updateAppointmentDate($noappointment_count);

                $this->deleteActivity($activity_id, $this->dataset_id);

            }


        }
    }

    /**
     * @throws ConnectionException
     */
    private function declineAppointment($appointment_request_id)
    {

        $response = Http::accept('application/json')
            ->delete('https://ish-services.thetechnodro.me/api/appointment/' . $appointment_request_id, [
                'send_to_pso' => true,
                'account_id' => config('pso-services.debug.account_id'),
                'base_url' => config('pso-services.debug.base_url'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password')
            ])->collect();

    }

    private function updateAppointmentDate($noappointment_count)
    {
        Log::debug('checking with count at ' . $noappointment_count);
        if ($noappointment_count > 3) {
            Log::debug('it is totally greater than 3');
            $this->relative_start += 7;
            Log::debug('offset is now ' . $this->relative_start);
        }
    }

    private function offerIsAvailable($appointed_response)
    {
        return $appointed_response['status'] === 200;
    }

    private function bestOffer($appointments)
    {
        return $appointments->all()['appointment_offers']['best_offer'];
    }

    private function hasOffers($appointment_response)
    {
//        return $appointment_response->first();
        if ($appointment_response['status'] !== 404) {
            $this->appointment_request_id = $appointment_response['appointment_offers']['appointment_request_id'];
            return true;
        } else {
            $this->appointment_request_id = $appointment_response['appointment_request_id'];
            return false;
        }

    }

    private function setAppointmentRequestID($appointment_response)
    {
        if ($appointment_response['status'] !== 404) {
            return $appointment_response['appointment_offers']['appointment_request_id'];

        } else {
            return $appointment_response['appointment_request_id'];

        }
    }

    /**
     * @throws ConnectionException
     */
    private function checkAppointed($appointment_request_id, $appointment_offer_id, $dataset_id)
    {

        $response = Http::accept('application/json')
            ->post('https://ish-services.thetechnodro.me/api/appointment/' . $appointment_request_id, [
                'appointment_offer_id' => $appointment_offer_id,
                'send_to_pso' => true,
                'account_id' => config('pso-services.debug.account_id'),
                'base_url' => config('pso-services.debug.base_url'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password'),
                'dataset_id' => $dataset_id
            ])->collect();

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    private function acceptAppointment($appointment_request_id, $appointment_offer_id, $dataset_id)
    {


        $response = Http::accept('application/json')
            ->patch('https://ish-services.thetechnodro.me/api/appointment/' . $appointment_request_id, [
                'appointment_offer_id' => $appointment_offer_id,
                'sla_type_id' => 'Primary SLA',
                'send_to_pso' => true,
                'account_id' => config('pso-services.debug.account_id'),
                'base_url' => config('pso-services.debug.base_url'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password'),
                'dataset_id' => $dataset_id
            ])->collect();

        return $response;


    }

    /**
     * @throws ConnectionException
     */
    private function deleteActivity($activity_id, $dataset_id)
    {

        $response = Http::accept('application/json')
            ->delete('https://ish-services.thetechnodro.me/api/activity/' . $activity_id, [
                'send_to_pso' => true,
                'account_id' => config('pso-services.debug.account_id'),
                'base_url' => config('pso-services.debug.base_url'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password'),
                'dataset_id' => $dataset_id
            ])->collect();

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    private function rltGetAppointment($activity_id, $activity_type_id, $duration, $lat, $long, $run_id, $dataset_id, $offset)
    {
        // set appointment template datetime = now
        $appointment_template_datetime_raw = Carbon::now()->startOfDay()->addDays($offset);

        $appointment_template_datetime = $appointment_template_datetime_raw->toDateTimeLocalString();
        Log::debug($appointment_template_datetime);
//        return [$appointment_template_datetime];

        // get appointment
        $response = Http::accept('application/json')
            ->post('https://ish-services.thetechnodro.me/api/appointment', [
                'activity_id' => $activity_id,
                'activity_type_id' => $activity_type_id,
                'dataset_id' => $dataset_id,
                'duration' => $duration,
                'sla_start' => $appointment_template_datetime,
                'sla_end' => $appointment_template_datetime_raw->addDays(21)->toDateTimeLocalString(),
                'sla_type_id' => 'APPOINTMENT',
                'lat' => $lat,
                'appointment_template_id' => 'STANDARD',
                'long' => $long,
                'base_url' => config('pso-services.debug.base_url'),
                'username' => config('pso-services.debug.username'),
                'password' => config('pso-services.debug.password'),
                'input_datetime' => Carbon::now()->toDateTimeLocalString(),
                'account_id' => config('pso-services.debug.account_id'),
                'description' => 'Get Appointments for ' . $activity_id,
                'appointment_template_datetime' => $appointment_template_datetime,
                'appointment_template_duration' => 21,
                'timezone' => config('pso-services.defaults.timezone'),
                'send_to_pso' => true,
                'run_id' => $run_id
            ])->collect();
        return $response;
    }
}
