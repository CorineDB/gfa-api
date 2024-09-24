<?php

namespace App\Http\Controllers;

use App\Http\Requests\membre_mission_de_controle\StoreRequest;
use App\Http\Requests\membre_mission_de_controle\UpdateRequest;
use Core\Services\Interfaces\MembreMissionDeControleServiceInterface;

class MembreMissionDeControleController extends Controller
{
    /**
     * @var service
     */
    private $membreMissionDeControleService;

    /**
     * Instantiate a new MembreMissionDeControleController instance.
     * @param MissionDeControleServiceInterface $membreMissionDeControleerviceInterface
     */
    public function __construct(MembreMissionDeControleServiceInterface $membreMissionDeControleServiceInterface)
    {
        $this->middleware('permission:voir-une-mission-de-controle')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-mission-de-controle')->only(['update']);
        $this->middleware('permission:creer-une-mission-de-controle')->only(['store']);
        $this->middleware('permission:supprimer-une-mission-de-controle')->only(['destroy']);

        $this->membreMissionDeControleService = $membreMissionDeControleServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->membreMissionDeControleService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->membreMissionDeControleService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idMissionDeControle
     * @param  int $idMembre
     * @return \Illuminate\Http\Response
     */
    public function show($idMissionDeControle, $idMembre)
    {
        return $this->membreMissionDeControleService->findById($idMembre);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idMissionDeControle
     * @param  int $idMembre
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idMissionDeControle, $idMembre)
    {
        return $this->membreMissionDeControleService->update($idMembre, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $idMissionDeControle
     * @param  int $idMembre
     * @return \Illuminate\Http\Response
     */
    public function destroy($idMissionDeControle, $idMembre)
    {
        return $this->membreMissionDeControleService->deleteById($idMembre);
    }
}
