<?php

namespace App\Http\Controllers;

use App\Http\Requests\evaluations_de_gouvernance\EvaluationParticipantRequest;
use App\Http\Requests\evaluations_de_gouvernance\StoreRequest;
use App\Http\Requests\evaluations_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\EvaluationDeGouvernanceServiceInterface;

class EvaluationDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $evaluationDeGouvernanceService;

    /**
     * Instantiate a new EvaluationDeGouvernanceController instance.
     * @param EvaluationDeGouvernanceController $evaluationDeGouvernanceServiceInterface
     */
    public function __construct(EvaluationDeGouvernanceServiceInterface $evaluationDeGouvernanceServiceInterface)
    {
        $this->evaluationDeGouvernanceService = $evaluationDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->evaluationDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->evaluationDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->evaluationDeGouvernanceService->findById($id);
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
        return $this->evaluationDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->evaluationDeGouvernanceService->deleteById($id);
    }

    public function organisations($id)
    {
        return $this->evaluationDeGouvernanceService->organisations($id);
    }

    public function soumissions($id)
    {
        return $this->evaluationDeGouvernanceService->soumissions($id);
    }

    public function fiches_de_synthese($id)
    {
        return $this->evaluationDeGouvernanceService->fiches_de_synthese($id);
    }

    public function formulaires_de_gouvernance($id)
    {
        return $this->evaluationDeGouvernanceService->formulaires_de_gouvernance($id);
    }

    public function envoi_mail_au_participants(EvaluationParticipantRequest $request, $id)
    {
        return $this->evaluationDeGouvernanceService->envoi_mail_au_participants($id, $request->all());
    }

    public function formulaire_factuel_de_gouvernance($id, $token)
    {
        return $this->evaluationDeGouvernanceService->formulaire_factuel_de_gouvernance($id, $token);
    }
    

    public function formulaire_de_perception_de_gouvernance($id, $participant_id, $token)
    {
        return $this->evaluationDeGouvernanceService->formulaire_de_perception_de_gouvernance($id, $participant_id, $token);
    }
    
}
