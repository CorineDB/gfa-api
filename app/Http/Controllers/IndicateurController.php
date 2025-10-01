<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur\AddAnneesCibleRequest;
use App\Http\Requests\indicateur\AddStrutureResponsableRequest;
use App\Http\Requests\indicateur\AddValueKeysRequest;
use App\Http\Requests\indicateur\FiltreRequest;
use App\Http\Requests\indicateur\RemoveValueKeysRequest;
use App\Http\Requests\indicateur\StoreRequest;
use App\Http\Requests\indicateur\UpdateRequest;
use App\Http\Requests\indicateur\UpdateValeursCiblesRequest;
use App\Http\Requests\indicateur\UpdateValeurDeBaseRequest;
use App\Http\Requests\indicateur\ChangeIndicateurTypeRequest;
use App\Http\Requests\indicateur\UpdateIndicateurCompletRequest;
use Illuminate\Http\Request;
use Core\Services\Interfaces\IndicateurServiceInterface;

class IndicateurController extends Controller
{
    /**
     * @var service
     */
    private $indicateurService;

    /**
     * Instantiate a new IndicateurController instance.
     * @param IndicateurServiceInterface $indicateurServiceInterface
     */
    public function __construct(IndicateurServiceInterface $indicateurServiceInterface)
    {
        //$this->middleware('role:unitee-de-gestion')->only(['store','update', 'destroy']);
        $this->middleware('permission:voir-un-indicateur')->only(['index', 'show', 'filtre']);
        $this->middleware('permission:modifier-un-indicateur')->only(['update', 'updateValeursCibles', 'updateValeurDeBase', 'updateIndicateurComplet', 'updateValeurCibleAnnee']);
        $this->middleware('permission:creer-un-indicateur')->only(['store']);
        $this->middleware('permission:supprimer-un-indicateur')->only(['destroy', 'deleteValeurCibleAnnee']);
        $this->middleware('permission:ajouter-une-cle-de-valeur-indicateur')->only(['addValueKeys']);
        $this->middleware('permission:supprimer-une-cle-de-valeur-indicateur')->only(['removeValueKeys']);
        $this->middleware('permission:voir-un-suivi-indicateur')->only(['suivis','checkSuivi']);
        $this->middleware('permission:modifier-un-indicateur')->only(['changeIndicateurType']);
        
        $this->indicateurService = $indicateurServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->indicateurService->all();
    }

    /**
     * Check if indicateur has suivi for a specifique year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSuivi($idIndicateur, $year)
    {
        return $this->indicateurService->checkSuivi($idIndicateur, $year);
    }

    /**
     * Récupérer la liste des suivis d'un indicateur
     *
     * @return JsonResponse
     */
    public function suivis($id){

        return $this->indicateurService->suivis($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        //$request["bailleurId"] = (array_key_exists("bailleurId", $request->all()) && isset($request["bailleurId"])) ? $request["bailleurId"] : Auth::user()->bailleur->id;

        return $this->indicateurService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idIndicateur)
    {
        return $this->indicateurService->findById($idIndicateur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idIndicateur)
    {
        //$request["bailleurId"] = ((array_key_exists("bailleurId", $request->all()) && isset($request["bailleurId"])) ? $request["bailleurId"] : Auth::user()->bailleur) ? Auth::user()->bailleur->id : $request["bailleurId"];

        return $this->indicateurService->update($idIndicateur, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idIndicateur)
    {
        return $this->indicateurService->deleteById($idIndicateur);
    }

    public function filtre(FiltreRequest $request)
    {
        return $this->indicateurService->filtre($request->all());
    }

    /**
     * Add new keys
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function addStrutureResponsable(AddStrutureResponsableRequest $request, $idIndicateur)
    {
        return $this->indicateurService->addStrutureResponsable($idIndicateur, $request->all());
    }

    /**
     * Add new keys
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAnneesCible(AddAnneesCibleRequest $request, $idIndicateur)
    {
        return $this->indicateurService->addAnneesCible($idIndicateur, $request->all());
    }

    /**
     * Add new keys
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function addValueKeys(AddValueKeysRequest $request, $idIndicateur)
    {
        //$request["bailleurId"] = ((array_key_exists("bailleurId", $request->all()) && isset($request["bailleurId"])) ? $request["bailleurId"] : Auth::user()->bailleur) ? Auth::user()->bailleur->id : $request["bailleurId"];

        return $this->indicateurService->addValueKeys($idIndicateur, $request->all());
    }

    /**
     * Remove new keys
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeValueKeys(RemoveValueKeysRequest $request, $idIndicateur)
    {
        //$request["bailleurId"] = ((array_key_exists("bailleurId", $request->all()) && isset($request["bailleurId"])) ? $request["bailleurId"] : Auth::user()->bailleur) ? Auth::user()->bailleur->id : $request["bailleurId"];

        return $this->indicateurService->removeValueKeys($idIndicateur, $request->all());
    }

    /**
     * Modifier les valeurs cibles d'un indicateur
     *
     * @param UpdateValeursCiblesRequest $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateValeursCibles(UpdateValeursCiblesRequest $request, $idIndicateur)
    {
        return $this->indicateurService->updateValeursCibles($idIndicateur, $request->all());
    }

    /**
     * Modifier une valeur cible pour une année spécifique
     *
     * @param Request $request
     * @param int $idIndicateur
     * @param int $annee
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateValeurCibleAnnee(Request $request, $idIndicateur, $annee)
    {
        $request->validate([
            'valeurCible' => 'required'
        ]);

        return $this->indicateurService->updateValeurCibleAnnee($idIndicateur, $annee, $request->get('valeurCible'));
    }

    /**
     * Supprimer une valeur cible pour une année donnée
     *
     * @param int $idIndicateur
     * @param int $annee
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteValeurCibleAnnee($idIndicateur, $annee)
    {
        return $this->indicateurService->deleteValeurCibleAnnee($idIndicateur, $annee);
    }

    /**
     * Modifier la valeur de base d'un indicateur
     *
     * @param UpdateValeurDeBaseRequest $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateValeurDeBase(UpdateValeurDeBaseRequest $request, $idIndicateur)
    {
        return $this->indicateurService->updateValeurDeBase($idIndicateur, $request->all());
    }

    /**
     * Changer le type d'indicateur (agrégé ↔ simple)
     *
     * @param ChangeIndicateurTypeRequest $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeIndicateurType(ChangeIndicateurTypeRequest $request, $idIndicateur)
    {
        return $this->indicateurService->changeIndicateurType($idIndicateur, $request->all());
    }

    /**
     * Modifier complètement un indicateur
     *
     * @param UpdateIndicateurCompletRequest $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateIndicateurComplet(UpdateIndicateurCompletRequest $request, $idIndicateur)
    {
        return $this->indicateurService->updateIndicateurComplet($idIndicateur, $request->all());
    }
}
