<?php

namespace App\Services;

use App\Classes\PSOActivity;
use App\Classes\PSOActivitySkill;
use App\Classes\PSOActivitySLA;
use App\Classes\PSOActivityStatus;
use App\Classes\PSOLocation;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;
use stdClass;

class IFSActivityService
{
    private string $pso_token;
    private string $pso_env;
    private string $pso_url;
    private string $dataset_id;
    private string $datetime_start;
    private string $pso_appt_endpoint;
    private string $pso_data_endpoint;
    private string $pso_is_blocking_template;
    private stdClass $activity_slot;
    private PSOActivity $pso_activity;

    public function __construct($token, $activity_data, $is_ab_request = false, $slot = null)
    {
        $this->pso_token = $token;
        $this->pso_env = config('ifs.pso.pso_environment');
        $this->pso_is_blocking_template = config('ifs.pso.pso_is_blocking_template');
        $this->pso_url = config('ifs.pso.' . $this->pso_env . '.base_url');
        $this->pso_appt_endpoint = config('ifs.pso.pso_appt_endpoint_path');
        $this->pso_data_endpoint = config('ifs.pso.pso_data_endpoint_path');
        $this->dataset_id = config('ifs.pso.' . $this->pso_env . '.dataset_id');
        $this->activity_slot = $slot ? (object)$slot : new stdClass();

        $this->pso_activity = $this->createActivity((object)$activity_data, $is_ab_request);

    }

    public static function create(...$params)
    {
        return new static(...$params);
    }

    private function createActivity(stdClass $activity_data, $is_ab_request): PSOActivity
    {

        if ($is_ab_request) {
            // todo`` expecting this ($this->activity_data->dttm_start) to be passed in at some point
            $this->datetime_start = isset($this->activity_data->dttm_start) ?: Carbon::now()->addDays(30)->toAtomString();
            $datetime_end = isset($this->activity_data->dttm_end) ?: Carbon::now()->addDays(40)->toAtomString();
        } else {
            $this->datetime_start = $this->activity_slot->window_start_datetime;
            $datetime_end = $this->activity_slot->window_end_datetime;
        }
        // sla_type depends on if AB or not         // status id depends on AB or not

        $pso_activity = PSOActivity::create($activity_data, $is_ab_request);
        foreach ($activity_data->task_skill as $skill) {
            $pso_activity->addActivitySkill(new PSOActivitySkill($skill['skill']));
        }
        $pso_activity->setActivityLocation(new PSOLocation(40.61627, -111.95641))
            ->addActivitySLA(new PSOActivitySLA($is_ab_request ? 'Appointment' : 'Primary SLA', $this->datetime_start, $datetime_end))
            ->setActivityStatus(new PSOActivityStatus($is_ab_request ? -1 : 0, 1, $activity_data->plan_task_dur_min));

        return $pso_activity;


    }

    private function AppointmentRequestData($appointment_template_id, $activity_id, $datetime_start = '')
    {
        return [
            "id" => Str::uuid()->getHex()->toString(),
            'activity_id' => $activity_id,
            "offer_expiry_datetime" => Carbon::now()->addMinutes(5)->toAtomString(),
            "appointment_template_id" => $appointment_template_id,
            // todo expecting this to be passed in at some point
            "appointment_template_datetime" => $this->datetime_start,
        ];
    }

    private function InputReferenceData($description)
    {
        return
            [
                'datetime' => Carbon::now()->toAtomString(),
                'id' => Str::orderedUuid()->getHex()->toString(),
                'description' => "$description",
                'input_type' => 'CHANGE',
                'organisation_id' => '2',
                'dataset_id' => $this->dataset_id,
                'schedule_data' => 'CONTINUOUS',
                'load_status' => '0',
                'duration' => 'P60D',
                'process_type' => 'APPOINTMENT',
            ];

    }

    private function ABRequestPayload($appointment_template_id)
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData('Appointment Request'),
                'Activity' => $this->pso_activity->ActivityToJson(),
                'Activity_SLA' => $this->pso_activity->ActivitySLAs(),
                'Activity_Status' => $this->pso_activity->ActivityStatus(), // always -1 for AB requests
                'Location' => $this->pso_activity->ActivityLocation(),
                'Activity_Skill' => $this->pso_activity->ActivitySkills(),
                'Appointment_Request' => $this->AppointmentRequestData($appointment_template_id, $this->pso_activity->getActivityID())

            ]
        ];

    }

    private function Appointment_Offer_Response($input_updated = true, $offer_id = -1)
    {
        return [
            'appointment_request_id' => $this->activity_slot->appointment_request_id,
            'appointment_offer_id' => $offer_id,
            'input_updated' => $input_updated
        ];
    }

    private function BookAppointmentPayload()
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData('Book Activity ' . $this->pso_activity->getActivityID()),
                'Activity' => $this->pso_activity->ActivityToJson(),
                'Activity_SLA' => $this->pso_activity->ActivitySLAs(),
                'Activity_Status' => $this->pso_activity->ActivityStatus(),
                'Location' => $this->pso_activity->ActivityLocation(),
                'Activity_Skill' => $this->pso_activity->ActivitySkills(),
                'Appointment_Offer_Response' => $this->Appointment_Offer_Response(true, $this->activity_slot->id),
                'Object_Deletion' => [
                    $this->ObjectDeletion('Activity_SLA', 'activity_id', $this->pso_activity->getActivityID() . '_appt', 'sla_type_id', 'SLA_APPOINTMENT', 'priority', '2', 'start_based', 'true'),
                    $this->ObjectDeletion('Activity', 'id', $this->pso_activity->getActivityID() . '_appt', 'visit_id', '1', '', '', '', ''),

                ]
            ]
        ];

    }

    private function IsAppointedPayload()
    {
        return [
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $this->InputReferenceData('Check Appointed'),
                'Appointment_Offer_Response' => $this->Appointment_Offer_Response(false, $this->activity_slot->id),
            ]
        ];

    }

    public function ObjectDeletion($obj_type, $objname1, $pk1, $objname2 = null, $pk2 = null, $objname3 = null, $pk3 = null, $objname4 = null, $pk4 = null)
    {
        return [
            'object_pk1' => $pk1,
            'object_pk2' => $pk2,
            'object_pk3' => $pk3,
            'object_pk4' => $pk4,
            'object_type_id' => $obj_type,
            'object_pk_name1' => $objname1,
            'object_pk_name2' => $objname2,
            'object_pk_name3' => $objname3,
            'object_pk_name4' => $objname4,
        ];
    }

    public function isAppointed()
    {
        $appointed = Http::withHeaders(['apiKey' => $this->pso_token])
            ->post('https://' . $this->pso_url . $this->pso_appt_endpoint, $this->IsAppointedPayload());


        return collect(collect($appointed
            ->collect()->get('dsScheduleData'))->get('Appointment_Summary'))->get('appointed');

    }

    public function BookAppointment()
    {

        if (!$this->pso_is_blocking_template) {

            if (!$this->isAppointed()) {
                // GTFO  - worst case // slot is taken
                return 'gtfo';
            }
            // slot is not taken, proceed to regular process?
        }
        // regular process blocking

        $request = Http::withHeaders(['apiKey' => $this->pso_token])
            ->post('https://' . $this->pso_url . $this->pso_data_endpoint, $this->BookAppointmentPayload());
        return $request->collect();
    }


    public function GetAppointmentSlots($ab_template_id)
    {
        // build AB payload
        $abrequest = $this->ABRequestPayload(
            $ab_template_id
        );

//        dd ($abrequest);

        clock()->info('sending AB request');
        // send AB request
        $request = Http::withHeaders(['apiKey' => $this->pso_token])
            ->post('https://' . $this->pso_url . $this->pso_appt_endpoint, $abrequest);

        clock()->info($request);

        // filter responses
        $offers = collect(collect($request
            ->collect()->get('dsScheduleData'))->get('Appointment_Offer'));

        // set offers as collections
        return $offers->map(function ($offer) {
            return (object)$offer;
        });


    }
}
