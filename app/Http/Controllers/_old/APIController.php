<?php

namespace App\Http\Controllers;

use App\Models\Token;
use app\Services\IFSActivityService;
use app\Services\IFSAuthService;
use app\Services\IFSResourceService;
use app\Services\IFSTaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class APIController extends Controller
{


    private function getToken(string $platform = 'pso', $force_reset = false)
    {
        $current_token = Token::latest()->where('name', $platform)->first();

        if (!$current_token || !$current_token->is_valid_token || $force_reset) {
            $current_token = Token::updateOrCreate(
                ['name' => $platform],
                ['token' => (new IFSAuthService())->getToken($platform), 'token_expiry' => Carbon::now()]);
        }

        return $current_token->token;
    }

    public function getTask($task_id, $platform)
    {
        // todo do we need this?
        $task = IFSTaskService::create($this->getToken($platform), $task_id);
        return $task->getFSMTask();
    }

    public function taskExists(Request $request, $platform = 'fsm')
    {
        $request->validate([
            'task_id' => ['required', 'numeric']
        ]);
        // create instance of task
        $task = IFSTaskService::create($this->getToken($platform), $request->task_id)->getFSMTask();


        // if we get a 500 then we probably should reauth and run it again
        if (!$task->getFSMTask() == 500) {
            $task = IFSTaskService::create($this->getToken($platform, true), $request->task_id)->getFSMTask();

        }

        return response()->json($task->taskHTTPStatus(), $task->taskHTTPStatus());

    }

    public function bookAppointment(Request $request)
    {

        clock()->info($request->slot);
        IFSActivityService::create($this->getToken(), $request->task_data, false, $request->slot)->BookAppointment();
//        $activity->BookAppointment();
        return IFSTaskService::create($this->getToken('fsm'), $request->task_data['task_id'], $request->slot, $request->task_data)
            ->updateOrCreateTimeCommit()
            ->updateTaskStatus()
            ->getFSMTask();

    }

    public function getAppointments(Request $request, $appointment_template_id = 'STANDARD_4HR')
    {
        $offers = IFSActivityService::create($this->getToken(), $request->task_data, true)->GetAppointmentSlots($appointment_template_id);

        // find only available ones
        $available_slots = $offers->filter(function ($offer) {
            return $offer->available == "true";
        });

        $grouped = $available_slots->groupBy(function ($item) {
            return Carbon::create($item->offer_start_datetime)->toDateString();
        });


        // find the list of resources
        $resource_list = $available_slots->map(function ($slot) {
            return ['resource_id' => $slot->prospective_resource_id];
        })->unique();

        // normalize the values
        // get the values
        $values_data['original_values_keyed'] = $available_slots->pluck('offer_value', 'id');
        $values_data['original_values'] = $available_slots->pluck('offer_value');
        $values_data['max_value'] = $available_slots->max('offer_value');
        $values_data['min_value'] = $available_slots->min('offer_value');
        foreach ($values_data['original_values_keyed'] as $key => $value) {
            $normalized = $this->normalize($value, $values_data['min_value'], $values_data['max_value']);
            switch (true) {
                case ($normalized) < 2:
                    $label = 'good';
                    break;
                case ($normalized) < 3:
                    $label = 'better';
                    break;
                case ($normalized) < 4:
                    $label = 'best';
                    break;
            }
            $values_data['normalized_values'][$key] = [
                'id' => $key,
                'original_offer_value' => $value,
                'normalized_offer_value' => $normalized,
                'label' => $label
            ];
        }

        $resource_names = [];

//        // make them lookupable
        clock()->info('right before resource lookup');

        if (config('ifs.app_params.requires_tech_lookup')) {
            foreach ($resource_list as $resource) {
                clock()->info($resource['resource_id']);
                $threesixty_resource = IFSResourceService::createResource($this->getToken('fsm'), $resource['resource_id']);
                clock()->info($threesixty_resource);

                if ($threesixty_resource) {
                    $resource_names[$resource['resource_id']] = [
                        'resource_id' => $resource['resource_id'],
                        'resource_name' => $threesixty_resource->getFSMResourceName(),
                        'resource_photo' => 'https://picsum.photos/id/' . $resource['resource_id'] . '/640/480'
                    ];
                }
            }
        }

        // find the max value
        $max_value = $available_slots->max('offer_value');

        // find the slot containing the max value
        $best_slot = $available_slots->filter(function ($slot) use ($max_value) {
            return $slot->offer_value == $max_value;
        })->first();

        return [
            'offers' => $offers,
            'available_slots' => $available_slots,
            'max_value' => $max_value,
            'best_slot' => $best_slot,
            'resources' => $resource_names,
            'initial_window' => [],
            'values_data' => $values_data,
            'grouped_slots' => $grouped
        ];
    }

    private function normalize($value, $min, $max)
    {
        return (config('ifs.app_params.normalize.normalize_max') - config('ifs.app_params.normalize.normalize_min'))
            / ($max - $min)
            * ($value - $max) + config('ifs.app_params.normalize.normalize_max');


    }
}
