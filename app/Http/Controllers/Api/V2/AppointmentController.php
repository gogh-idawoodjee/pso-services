<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\AppointmentRequest;

use App\Services\V2\AppointmentService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    use PSOAssistV2;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request): JsonResponse
    {

        return $this->executeAuthenticatedAction($request, function (AppointmentRequest $req) {
            // so we have the token now in data_get($req, 'environment.token')

            // we should send that the activity service? // all our services should accept a token
            $appointmentService = new AppointmentService(

                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $appointmentService->getAppointment();
        });


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
