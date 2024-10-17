<?php

namespace App\Http\Controllers;

use App\Http\Requests\enquete_de_collecte\AppreciationRequest;
use App\Http\Requests\enquete_de_collecte\CollecteRequest;
use App\Http\Requests\enquete_de_collecte\StoreRequest;
use App\Http\Requests\enquete_de_collecte\UpdateRequest;
use Core\Services\Interfaces\EnqueteDeCollecteServiceInterface;
use Illuminate\Http\Request;

class EnqueteDeCollecteController extends Controller
{
    /**
     * @var service
     */
    private $enqueteDeCollecteService;

    /**
     * Instantiate a new EnqueteDeCollecteController instance.
     * @param EnqueteDeCollecteServiceInterface $enqueteDeCollecteServiceInterface
     */
    public function __construct(EnqueteDeCollecteServiceInterface $enqueteDeCollecteServiceInterface)
    {
        $this->enqueteDeCollecteService = $enqueteDeCollecteServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->enqueteDeCollecteService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->enqueteDeCollecteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->enqueteDeCollecteService->findById($id);
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
        return $this->enqueteDeCollecteService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->enqueteDeCollecteService->deleteById($id);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function profil_de_gouvernance($id, $organisationId)
    {
        return $this->enqueteDeCollecteService->resultats($id, $organisationId);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $enqueteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function resultats($enqueteId, $organisationId)
    {
        return $this->enqueteDeCollecteService->resultats($enqueteId, $organisationId);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resultat_appreciations(Request $request, $enqueteId, $organisationId)
    {
        return $this->enqueteDeCollecteService->resultat_appreciations($enqueteId, $organisationId, ["type" => $request->query('type')]);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $enqueteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reponses_collecter($enqueteId)
    {
        return $this->enqueteDeCollecteService->reponses_collecter($enqueteId);
    }

    /**
     * Enregistrer les donnees d'une collecte pour le compte d'une enquete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function collecter(CollecteRequest $request, $enqueteId)
    {
        return $this->enqueteDeCollecteService->collecter($enqueteId, $request->all());
    }

    /**
     * Appreciation de l'etat de gouvernance
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function appreciation(AppreciationRequest $request, $enqueteId)
    {
        return $this->enqueteDeCollecteService->appreciation($enqueteId, $request->all());
    }

    /**
     * Liste des organisations qui n'ont pas encore repondu a une enquete
     * 
     * @param  String  $enqueteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function surveyEligibleParticipants($enqueteId)
    {
        return $this->enqueteDeCollecteService->surveyEligibleParticipants($enqueteId);
    }

    /**
     * Retrieve a list of organizations that have submitted responses to a survey.
     *
     * @param  String  $enqueteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function surveySubmittedParticipants($enqueteId)
    {
        return $this->enqueteDeCollecteService->surveySubmittedParticipants($enqueteId);
    }
}