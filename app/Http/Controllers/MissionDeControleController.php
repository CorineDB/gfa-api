<?php

namespace App\Http\Controllers;

use App\Http\Requests\mission_de_controle\StoreRequest;
use App\Http\Requests\mission_de_controle\UpdateRequest;
use Core\Services\Interfaces\MissionDeControleServiceInterface;

class MissionDeControleController extends Controller
{

    /**
     * @var service
     */
    private $missionDeControleServiceInterface;

    /**
     * Instantiate a new MissionDeControleController instance.
     * @param MissionDeControleServiceInterface $missionDeControleServiceInterface
     */
    public function __construct(MissionDeControleServiceInterface $missionDeControleServiceInterface)
    {
        $this->middleware('permission:voir-une-mission-de-controle')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-mission-de-controle')->only(['update']);
        $this->middleware('permission:creer-une-mission-de-controle')->only(['store']);
        $this->middleware('permission:supprimer-une-mission-de-controle')->only(['destroy']);
        
        $this->missionDeControleServiceInterface = $missionDeControleServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->missionDeControleServiceInterface->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->missionDeControleServiceInterface->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idMissionDeControle
     * @return \Illuminate\Http\Response
     */
    public function show($idMissionDeControle)
    {
        return $this->missionDeControleServiceInterface->findById($idMissionDeControle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idMissionDeControle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idMissionDeControle)
    {
        unset($request['programmeId']);
        return $this->missionDeControleServiceInterface->update($idMissionDeControle, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MissionDeControle  $idMissionDeControle
     * @return \Illuminate\Http\Response
     */
    public function destroy($idMissionDeControle)
    {
        return $this->missionDeControleServiceInterface->deleteById($idMissionDeControle);
    }

}
