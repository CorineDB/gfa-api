<?php

namespace App\Http\Controllers;

use App\Http\Requests\alerteConfig\UpdateRequest;
use Core\Services\Interfaces\AlerteConfigServiceInterface;
use Illuminate\Http\Request;

class AlerteConfigController extends Controller
{
    /**
     * @var service
     */
    private $alerteConfigService;

    /**
     * Instantiate a new AlerteConfigController instance.
     * @param AlerteConfigServiceInterface $alerteConfigServiceInterface
     */
    public function __construct(AlerteConfigServiceInterface $alerteConfigServiceInterface)
    {
        $this->middleware('permission:voir-une-configuration-alerte')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-configuration-alerte')->only(['update']);
        $this->middleware('permission:creer-une-configuration-alerte')->only(['store']);
        $this->middleware('permission:supprimer-une-configuration-alerte')->only(['destroy']);

        $this->alerteConfigService = $alerteConfigServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->alerteConfigService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $alerteConfig
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $alerteConfig
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $alerteConfig)
    {
        return $this->alerteConfigService->update($alerteConfig, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $alerteConfig
     * @return \Illuminate\Http\Response
     */
    public function destroy($alerteConfig)
    {
        //
    }

}
