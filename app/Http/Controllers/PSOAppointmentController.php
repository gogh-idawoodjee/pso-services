<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PSOAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }


    /**
     * request the appointment
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {


        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'activity_id' => 'string|required',
            'activity_type_id' => 'string|required',
            'duration' => 'integer|lt:1440|required:',
            'sla_start' => 'date|before:sla_end|required',
            'sla_end' => 'date|after:sla_start|required',
            'sla_type_id' => 'string|required',
            'appointment_template_id' => 'string|required',
            'lat' => 'numeric|between:-90,90|required',
            'long' => 'numeric|between:-180,180|required'
        ]);

        Validator::make($request->all(), [
            'token' => Rule::requiredIf($request->send_to_pso == true && !$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();

        $appointment = new IFSPSOAppointmentService($request);


    }

    /**
     * accept the appointment slot.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * reject the appointment slots.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
