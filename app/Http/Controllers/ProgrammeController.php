<?php

namespace App\Http\Controllers;

use App\Http\Requests\programme\KoboPreviewRequest;
use Illuminate\Http\Request;

use App\Http\Requests\programme\StoreRequest;
use App\Http\Requests\programme\KoboRequest;
use App\Http\Requests\programme\RapportMailRequest;
use App\Http\Requests\programme\RapportStoreRequest;
use App\Http\Requests\programme\UpdateRequest;
use Core\Services\Interfaces\ProgrammeServiceInterface;

class ProgrammeController extends Controller
{
    /**
     * @var service
     */
    private $programmeService;

    /**
     * Instantiate a new ProjetController instance.
     * @param ProgrammeServiceInterface $programmeServiceInterface
     */
    public function __construct(ProgrammeServiceInterface $programmeServiceInterface)
    {
        $this->middleware('permission:voir-un-programme')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-programme')->only(['update']);
        $this->middleware('permission:creer-un-programme')->only(['store']);
        $this->middleware('permission:supprimer-un-programme')->only(['destroy']);

        $this->middleware('permission:voir-revision-ptab')->only(['scopes']);
        $this->middleware('permission:voir-un-projet')->only(['projets']);
        $this->middleware('permission:voir-une-outcome')->only(['composantes']);
        $this->middleware('permission:voir-un-output')->only(['sousComposantes']);
        $this->middleware('permission:voir-une-activite')->only(['activites']);
        $this->middleware('permission:voir-une-tache')->only(['taches']);
        $this->middleware('permission:voir-un-decaissement')->only(['decaissements']);
        $this->middleware('permission:voir-un-site')->only(['sites']);
        $this->middleware('permission:voir-une-categorie')->only(['categories']);
        $this->middleware('permission:voir-un-suivi-financier')->only(['suiviFinanciers']);
        $this->middleware('permission:voir-une-organisation')->only(['evaluations_organisations']);
        $this->middleware('permission:voir-statistique-evolution-des-profiles-de-gouvernance-au-fil-du-temps')->only(['scoresAuFilDuTemps']);
        $this->middleware('permission:voir-cadre-de-rendement')->only(['cadre_de_mesure_rendement']);



        $this->programmeService = $programmeServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->programmeService->all();
    }


    /**
     * Recupérer la liste des entreprises executante d'un programme
     * @param int|String $id. $id qui represente l'ID du programme
     * @return JsonResponse
     */
    public function entreprisesExecutante($id)
    {
        return $this->programmeService->entreprisesExecutante($id);
    }


    /**
     * Recupérer la liste des mods d'un programme
     * @param int|String $id. $id qui represente l'ID du programme
     * @return JsonResponse
     */
    public function mods($id)
    {
        return $this->programmeService->mods($id);
    }


    /**
     * Recupérer la liste des bailleurs d'un programme
     * @param int|String $id. $id qui represente l'ID du programme
     * @return JsonResponse
     */
    public function bailleurs($id)
    {
        return $this->programmeService->bailleurs($id);
    }

    public function structures($id)
    {
        return $this->programmeService->structures($id);
    }


    /**
     * Recupérer la liste des projets d'un programme
     * @param int|String $id. $id qui represente l'ID du programme
     * @return JsonResponse
     */
    public function projets($id)
    {
        return $this->programmeService->projets($id);
    }

    /**
     * Recupérer la liste des scopes d'un programme
     * @param int|String $id. $id qui represente l'ID du programme
     * @return JsonResponse
     */
    public function scopes($id)
    {
        return $this->programmeService->scopes($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->programmeService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->programmeService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->programmeService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->programmeService->deleteById($id);
    }

    public function composantes($id)
    {
        return $this->programmeService->composantes($id);
    }


    public function sousComposantes($id)
    {
        return $this->programmeService->sousComposantes($id);
    }


    public function activites($id)
    {
        return $this->programmeService->activites($id);
    }

    public function eActivites($id)
    {
        return $this->programmeService->eActivites($id);
    }

    public function taches($id)
    {
        return $this->programmeService->taches($id);
    }


    public function decaissements($id)
    {
        return $this->programmeService->decaissements($id);
    }

    public function sinistres($id)
    {
        return $this->programmeService->sinistres($id);
    }

    public function sites($id)
    {
        return $this->programmeService->sites($id);
    }

    public function categories($id)
    {
        return $this->programmeService->categories($id);
    }

    public function cadre_de_mesure_rendement($id)
    {
        return $this->programmeService->cadre_de_mesure_rendement($id);
    }

    public function scoresAuFilDuTemps($organisationId)
    {
        return $this->programmeService->scores_au_fil_du_temps($organisationId);
    }

    public function evaluations_organisations(?string $id = null)
    {
        return $this->programmeService->evaluations_organisations();
    }

    public function stats_evaluations_de_gouvernance_organisations(?string $id = null)
    {
        return $this->programmeService->stats_evaluations_de_gouvernance_organisations();
    }

    public function suiviFinanciers($id)
    {
        return $this->programmeService->suiviFinanciers($id);
    }

    public function modPassations($id)
    {
        return $this->programmeService->modPassations($id);
    }

    public function missionDeControlePassations($id)
    {
        return $this->programmeService->missionDeControlePassations($id);
    }

    public function maitriseOeuvres($id)
    {
        return $this->programmeService->maitriseOeuvres($id);
    }

    public function users($id)
    {
        return $this->programmeService->users($id);
    }

    public function entrepriseUsers($id)
    {
        return $this->programmeService->entrepriseUsers($id);
    }

    public function kobo()
    {
        return $this->programmeService->kobo();
    }

    public function koboUpdate()
    {
        return $this->programmeService->koboUpdate();
    }

    public function koboSuivie(KoboRequest $request)
    {
        return $this->programmeService->koboSuivie($request->all());
    }

    public function koboPreview(KoboPreviewRequest $request)
    {
        return $this->programmeService->koboPreview($request->all());
    }

    public function dashboard()
    {
        return $this->programmeService->dashboard();
    }

    public function rapport(RapportStoreRequest $request)
    {
        return $this->programmeService->rapport($request->all());
    }

    public function rapportSendMail(RapportMailRequest $request)
    {
        return $this->programmeService->rapportSendMail($request->all());
    }

    public function rapports()
    {
        return $this->programmeService->rapports();
    }

    public function updateRapport(Request $request, $id)
    {
        return $this->programmeService->updateRapport($request->all(), $id);
    }

    public function deleteRapport($id)
    {
        return $this->programmeService->deleteRapport($id);
    }

    public function emailRapports()
    {
        return $this->programmeService->emailRapports();
    }

    public function scores_au_fil_du_temps_reviser(?string $organisationId = null)
    {
        return $this->programmeService->scores_au_fil_du_temps_reviser($organisationId);
    }
}
