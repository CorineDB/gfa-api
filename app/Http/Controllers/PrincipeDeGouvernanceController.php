<?php

namespace App\Http\Controllers;

use App\Http\Requests\principe_de_gouvernance\StoreRequest;
use App\Http\Requests\principe_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\PrincipeDeGouvernanceServiceInterface;
use Illuminate\Support\Facades\Auth;

class PrincipeDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $principeDeGouvernanceService;

    /**
     * Instantiate a new PrincipeDeGouvernanceController instance.
     * @param PrincipeDeGouvernanceServiceInterface $principeDeGouvernanceServiceInterface
     */
    public function __construct(PrincipeDeGouvernanceServiceInterface $principeDeGouvernanceServiceInterface)
    {
        $this->principeDeGouvernanceService = $principeDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->principeDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->principeDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->principeDeGouvernanceService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->principeDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->principeDeGouvernanceService->deleteById($id);
    }

    /**
     * Criteres of a principe de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function criteres($id)
    {
        return $this->principeDeGouvernanceService->criteres($id);
    }

    /**
     * Indicateurs of a principe de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicateurs($id)
    {
        return $this->principeDeGouvernanceService->indicateurs($id);
    }

    /**
     * Formulaire de l'outil factuel
     *
     * @param  String  $progammeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function formulaire_factuel()
    {
        return $this->principeDeGouvernanceService->formulaire_factuel();
    }

    /**
     * Formulaire de l'outil de perception
     *
     * @param  String  $progammeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function formulaire_de_perception()
    {
        return $this->principeDeGouvernanceService->formulaire_de_perception();
    }
}
