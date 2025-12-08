<?php

namespace App\Http\Controllers;

use App\Http\Requests\activite\FiltreActiviteRequest;
use App\Http\Requests\activite\StoreActiviteRequest;
use App\Http\Requests\activite\UpdateActiviteRequest;
use App\Http\Requests\activite\DeplacerRequest;
use App\Http\Requests\activite\PpmRequest;
use App\Http\Requests\duree\StoreDureeRequest;
use App\Http\Requests\duree\UpdateDureeRequest;
use App\Http\Requests\activite\FiltreSuiviRequest;
use Core\Services\Interfaces\ActiviteServiceInterface;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    /**
     * @var service
     */
    private $activiteService;

    /**
     * Instantiate a new ActiviteController instance.
     * @param ActiviteServiceInterface $activiteServiceInterface
     */
    public function __construct(ActiviteServiceInterface $activiteServiceInterface)
    {
        $this->middleware('permission:voir-une-activite')->only(['index', 'show', 'filtre']);
        $this->middleware('permission:modifier-une-activite')->only(['update', 'deplacer', 'ajouterDuree', 'modifierDuree']);
        $this->middleware('permission:creer-une-activite')->only(['store']);
        $this->middleware('permission:supprimer-une-activite')->only(['destroy']);


        $this->middleware('permission:voir-une-tache')->only(['taches']);
        $this->middleware('permission:voir-un-suivi')->only(['suivis']);
        $this->middleware('permission:voir-un-suivi-financier')->only(['suivis_financier']);

        $this->middleware('permission:voir-un-plan-de-decaissement')->only(['plansDeDecaissement']);

        $this->activiteService = $activiteServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->activiteService->all();
    }

    public function filtre(FiltreActiviteRequest $request)
    {
        return $this->activiteService->filtre($request->all());
    }

    public function filterActivities(FiltreActiviteRequest $request)
    {
        return $this->activiteService->filterActivities($request->all());
    }

    public function plansDeDecaissement($id)
    {
        return $this->activiteService->plansDeDecaissement($id);
    }

    /**
     * Liste des suivis d'une activite
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suivis($id)
    {
        return $this->activiteService->suivis($id);
    }

    /**
     * Liste des suivis financier d'une activite
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suivis_financier(FiltreSuiviRequest $request, $id)
    {
        return $this->activiteService->suivisFinancier($id, $request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActiviteRequest $request)
    {
        return $this->activiteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->activiteService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActiviteRequest $request, $activite)
    {
        return $this->activiteService->update($activite, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function destroy($activite)
    {
        return $this->activiteService->deleteById($activite);
    }

    public function taches($id)
    {
        return $this->activiteService->taches($id);
    }

    public function changeStatut(Request $request, $id)
    {

        $request->validate([
            'statut' => 'required|in:-1,0,1,2'
        ]);

        return $this->activiteService->changeStatut($id, $request->all());
    }

    public function ajouterDuree(StoreDureeRequest $request, $id)
    {
        return $this->activiteService->ajouterDuree($request->all(),$id );
    }

    public function modifierDuree(UpdateDureeRequest $request, $activiteId, $dureeId)
    {
        return $this->activiteService->modifierDuree($request->all(), $activiteId, $dureeId);
    }

    public function deplacer(DeplacerRequest $request, $id)
    {
        return $this->activiteService->deplacer($request->all(), $id);
    }

    public function ppm(PpmRequest $request)
    {
        return $this->activiteService->ppm($request->all());
    }
}
