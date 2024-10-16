<?php

namespace App\Http\Controllers;

use App\Http\Requests\resultat_cadre_de_rendement\CadreDeMesureRendementForm;
use App\Http\Requests\resultat_cadre_de_rendement\CadreDeMesureRendementFormRequest;
use App\Http\Requests\resultat_cadre_de_rendement\StoreRequest;
use App\Http\Requests\resultat_cadre_de_rendement\UpdateRequest;
use Core\Services\Interfaces\ResultatCadreDeRendementServiceInterface;

class ResultatCadreDeRendementController extends Controller
{
    /**
     * @var service
     */
    private $resultatCadreRendementService;

    /**
     * Instantiate a new ResultatCadreDeRendementController instance.
     * @param ResultatCadreDeRendementController $resultatCadreRendementServiceInterface
     */
    public function __construct(ResultatCadreDeRendementServiceInterface $resultatCadreRendementServiceInterface)
    {
        $this->resultatCadreRendementService = $resultatCadreRendementServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->resultatCadreRendementService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->resultatCadreRendementService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->resultatCadreRendementService->findById($id);
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
        return $this->resultatCadreRendementService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->resultatCadreRendementService->deleteById($id);
    }

    /**
     * Construction du cadre de mesure du rendement du programme ou d'un projet.
     * 
     * @param CadreDeMesureRendementFormRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function constituerCadreDeMesureRendement(CadreDeMesureRendementFormRequest $request)
    {
        return $this->resultatCadreRendementService->constituer_cadre_de_mesure_rendement($request->all());
    }

    /**
     * Display the cadre de mesure rendement for the programme.
     *
     * @param  int $projetId
     * @return \Illuminate\Http\Response
     */
    public function cadreDeMesureRendement()
    {
        // Retrieve the cadre de mesure rendement for the specified projet
        return $this->resultatCadreRendementService->cadreDeMesureRendement();
    }
}
