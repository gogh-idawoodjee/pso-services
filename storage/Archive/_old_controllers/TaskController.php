<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Services\_old\IFSAuthService;
use app\Services\IFSTaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    //
    public function index(Request $request)
    {
        return inertia('Welcome');
    }

    public function sandbox()
    {


////        // get task deets
//        $platform = 'fsm';
////        $task = new IFSTaskService($this->getToken($platform), '701104757');
//////        $task = new IFSTaskService($this->getToken($platform), '1234');
////        $task_data = $task->getFSMTask();
////        dd($task);
//////        return $task_data->place[0]->name;
////
////        // send that shit to PSO
////        $platform = 'pso';
//        $myurl = 'https://brinkshome-fsm-tst.ifs.cloud/odata/task/701031503?$expand=place($select=name,time_zone),task_skill($select=skill),time_commit($select=commit_dttm,start_dttm,tc_id,status)&$select=task_id,plan_task_dur_min,task_category,task_status,schedule_value,description,task_type,earliest_start_dttm,latest_start_dttm';
//        $this->fsm_task = Http::withHeaders(['Authorization' => $this->getToken('fsm')])->get($myurl);
//        $task_data = $this->fsm_task->object();
////        return $task_data;
//        $activity = new IFSActivityService($this->getToken('pso'), $task_data);
//        $offers = $activity->GetAppointmentSlots('STANDARD_PST');
//
//        // find only available ones
//        $available_slots = $offers->filter(function ($offer) {
//            return $offer->available == "true";
//        });
//
//        // find the list of resources
//        $resource_list = $available_slots->map(function ($slot) {
//            return ['resource_id' => $slot->prospective_resource_id];
//        })->unique();
//
//        // make them lookupable
//        foreach ($resource_list as $resource) {
//            $threesixty_resource = new IFSResourceService($this->getToken('fsm'), $resource['resource_id']);
//            $resource_names[$resource['resource_id']] = ['resource_id' => $resource['resource_id'], 'resource_name' => $threesixty_resource->getFSMResourceName()];
//        }
//
//        return $resource_names;
        function normalize($value, $min, $max)
        {
            $normalized = ($value - $min) / ($max - $min);
            return $normalized;

        }
    }

    public function show($task_id)
    {
        $platform = 'fsm';
        $task = new IFSTaskService($this->getToken($platform), $task_id);
        return inertia('Task', [
            'task_data' => $task->getFSMTask()
        ]);

    }

    /**
     * @param string $platform
     */
    private function getToken(string $platform = 'pso')
    {
        $current_token = Token::latest()->where('name', $platform)->first();

        if (!$current_token || !$current_token->is_valid_token) {
            $current_token = Token::updateOrCreate(
                ['name' => $platform],
                ['token' => (new IFSAuthService())->getToken($platform), 'token_expiry' => Carbon::now()]);
        }

        return $current_token->token;
    }

    private function ObjectDeletion($obj_type, $pk1, $pk2, $pk3, $pk4, $objname1, $objname2, $objname3, $objname4)
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

//    private function BookAppointmentPayload()
//    {
//        return [
//            'dsScheduleData' => [
//                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
//                'Input_Reference' => ['inputref'],
//                'Activity' => $this->ActivityData(),
//
//            ]
//        ];
//
//    }
}
