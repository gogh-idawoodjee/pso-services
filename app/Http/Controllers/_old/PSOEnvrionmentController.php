<?php

namespace App\Http\Controllers;

use App\Models\PsoDataset;
use App\Models\PsoEnvironment;
use App\Rules\PSOCanAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PSOEnvrionmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        //
        $environments = PsoEnvironment::with('datasets')->get();

        return $environments->firstWhere('id', 'cb847e5e-8747-4a02-9322-76530ef38a19')->datasets;

//        return view('environment')->with('environments', $environments);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        //
        $request->validate([
            'base_url' => ['required', new PSOCanAuth()],
            'username' => 'required',
            'password' => 'required',
            'name' => 'required',
            'account_id' => 'required|alpha_num',
            'dataset_id' => 'required',
            'rota_id' => 'required',
        ]);

        $environment = new PsoEnvironment();
        $environment->base_url = $request->base_url;
        $environment->username = $request->username;
        $environment->password = $request->password;
        $environment->account_id = $request->account_id;
        $environment->name = $request->name;

        $dataset = new PsoDataset();
        $dataset->dataset_id = $request->dataset_id;
        $dataset->rota_id = $request->rota_id;
        $environment->save();
        $environment->datasets()->save($dataset);
        return response($environment->load('datasets')->makeHidden('password'));

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
