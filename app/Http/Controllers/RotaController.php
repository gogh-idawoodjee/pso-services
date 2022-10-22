<?php

namespace App\Http\Controllers;

use App\Models\PsoEnvironment;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use Inertia\Inertia;

class RotaController extends Controller
{
    /**
     * Display a listing of the resource.
     ** @return Response|ResponseFactory
     */
    public function index()
    {
        //

//        return parse_url('https://thetechnodro.me:999/rotathingy/manual');
        return Inertia::render('Rota/Index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //


    }

    /**
     * Display the specified resource.
     *
     * @param PsoEnvironment $pso_environment
     * @return Response|ResponseFactory
     */
    public function show(PsoEnvironment $pso_environment)
    {
        $pso_environment->load('datasets');

//        return $pso_environment;
        return inertia('Rota/Env')->with('env', $pso_environment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
