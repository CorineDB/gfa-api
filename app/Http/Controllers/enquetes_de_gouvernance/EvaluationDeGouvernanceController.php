<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\EvaluationParticipantRequest;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\EvaluationDeGouvernanceServiceInterface;

class EvaluationDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $evaluationDeGouvernanceService;

    /**
     * Instantiate a new EvaluationDeGouvernanceController instance.
     * @param EvaluationDeGouvernanceServiceInterface $evaluationDeGouvernanceServiceInterface
     */
    public function __construct(EvaluationDeGouvernanceServiceInterface $evaluationDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-une-evaluation-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-evaluation-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-une-evaluation-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-une-evaluation-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-une-organisation')->only(['organisations']);
        $this->middleware('permission:voir-une-soumission')->only(['soumissions']);
        $this->middleware('permission:voir-une-recommandation')->only(['recommandations']);
        $this->middleware('permission:voir-une-action-a-mener')->only(['actions_a_mener']);
        $this->middleware('permission:voir-plan-action')->only(['feuille_de_route']);
        $this->middleware('permission:voir-une-fiche-de-synthese')->only(['fiches_de_synthese']);
        $this->middleware('permission:voir-resultats-evaluation')->only(['resultats_syntheses']);
        $this->middleware('permission:voir-un-formulaires-de-gouvernance')->only(['formulaires_de_gouvernance']);
        $this->middleware('permission:voir-formulaire-factuel')->only(['formulaire_factuel', 'formulaire_factuel_de_gouvernance']);

        $this->middleware('permission:envoyer-un-rappel-soumission')->only(['rappel_soumission']);
        $this->middleware('permission:envoyer-une-invitation')->only(['envoi_mail_au_participants']);


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
     * @param  \App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance  $paye
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
     * @param  \App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->evaluationDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance  $paye
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

    public function soumissions_enquete($id)
    {
        return $this->evaluationDeGouvernanceService->soumissions_enquete($id);
    }

    public function soumissions_enquete_factuel($id)
    {
        return $this->evaluationDeGouvernanceService->soumissions_enquete_factuel($id);
    }

    public function soumissions_enquete_de_perception($id)
    {
        return $this->evaluationDeGouvernanceService->soumissions_enquete_de_perception($id);
    }

    public function recommandations($id)
    {
        return $this->evaluationDeGouvernanceService->recommandations($id);
    }

    public function actions_a_mener($id)
    {
        return $this->evaluationDeGouvernanceService->actions_a_mener($id);
    }

    public function principes($id)
    {
        return $this->evaluationDeGouvernanceService->principes_de_gouvernance($id);
    }

    public function feuille_de_route($id)
    {
        return $this->evaluationDeGouvernanceService->feuille_de_route($id);
    }

    public function fiches_de_synthese($id)
    {
        return $this->evaluationDeGouvernanceService->fiches_de_synthese($id);
    }

    public function fiches_de_synthese_with_organisations_classement($id)
    {
        return $this->evaluationDeGouvernanceService->fiches_de_synthese_with_organisations_classement($id);
    }

    public function resultats_syntheses($id)
    {
        return $this->evaluationDeGouvernanceService->resultats_syntheses($id);
    }

    public function voir_resultats_syntheses_avec_classement_des_organisations($id)
    {
        return $this->evaluationDeGouvernanceService->classement_resultats_syntheses_des_organisation($id);
    }

    public function formulaires_de_gouvernance($id)
    {
        return $this->evaluationDeGouvernanceService->formulaires_de_gouvernance($id);
    }

    public function envoi_mail_au_participants(EvaluationParticipantRequest $request, $id)
    {
        return $this->evaluationDeGouvernanceService->envoi_mail_au_participants($id, $request->all());
    }

    public function formulaire_factuel($id)
    {
        return $this->evaluationDeGouvernanceService->formulaire_factuel($id);
    }

    public function rappel_soumission($id)
    {
        return $this->evaluationDeGouvernanceService->rappel_soumission($id);
    }

    public function formulaire_factuel_de_gouvernance(string $token)
    {
        return $this->evaluationDeGouvernanceService->formulaire_factuel_de_gouvernance($token);
    }

    public function formulaire_de_perception_de_gouvernance(string $participant_id, string $token)
    {
        return $this->evaluationDeGouvernanceService->formulaire_de_perception_de_gouvernance($participant_id, $token);
    }

}
