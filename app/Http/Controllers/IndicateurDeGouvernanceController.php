<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur_de_gouvernance\StoreRequest;
use App\Http\Requests\indicateur_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\IndicateurDeGouvernanceServiceInterface;

class IndicateurDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $indicateurDeGouvernanceService;

    /**
     * Instantiate a new IndicateurDeGouvernanceController instance.
     * @param IndicateurDeGouvernanceServiceInterface $indicateurDeGouvernanceServiceInterface
     */
    public function __construct(IndicateurDeGouvernanceServiceInterface $indicateurDeGouvernanceServiceInterface)
    {
        $this->indicateurDeGouvernanceService = $indicateurDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->indicateurDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->indicateurDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->indicateurDeGouvernanceService->findById($id);
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
        return $this->indicateurDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->indicateurDeGouvernanceService->deleteById($id);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function observations($id)
    {
        return $this->indicateurDeGouvernanceService->observations($id);
    }
}
