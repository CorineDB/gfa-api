<?php

namespace App\Http\Controllers;

use App\Http\Requests\suiviIndicateur\DateSuivieRequest;
use App\Http\Requests\suiviIndicateur\FilterRequest;
use App\Http\Requests\suiviIndicateur\StoreRequest;
use App\Http\Requests\suiviIndicateur\SuiviKoboRequest;
use App\Http\Requests\suiviIndicateur\UpdateRequest;
use Core\Services\Interfaces\SuiviIndicateurServiceInterface;
use Illuminate\Http\Request;

class SuiviIndicateurController extends Controller
{
    /**
     * @var service
     */
    private $suiviIndicateurService;

    /**
     * Instantiate a new SuiviIndicateurController instance.
     * @param SuiviIndicateurServiceInterface $suiviIndicateurServiceInterface
     */
    public function __construct(SuiviIndicateurServiceInterface $suiviIndicateurServiceInterface)
    {
        $this->middleware('permission:voir-un-suivi-indicateur')->only(['index', 'show', 'filtre', 'dateSuivie']);
        $this->middleware('permission:modifier-un-suivi-indicateur')->only(['update']);
        $this->middleware('permission:creer-un-suivi-indicateur')->only(['store']);
        $this->middleware('permission:supprimer-un-suivi-indicateur')->only(['destroy']);
        $this->middleware('permission:valider-un-suivi-indicateur')->only(['valider']);

        $this->suiviIndicateurService = $suiviIndicateurServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->suiviIndicateurService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->suiviIndicateurService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idSuiviIndicateurs
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSuiviIndicateur)
    {
        return $this->suiviIndicateurService->findById($idSuiviIndicateur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idSuiviIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function filtre(FilterRequest $request)
    {
        return $this->suiviIndicateurService->filter($request->all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idSuiviIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSuiviIndicateur)
    {
        return $this->suiviIndicateurService->update($idSuiviIndicateur, $request->all());
    }

    public function dateSuivie(DateSuivieRequest $request)
    {
        return $this->suiviIndicateurService->dateSuivie($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idSuiviIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSuiviIndicateur)
    {
        return $this->suiviIndicateurService->deleteById($idSuiviIndicateur);
    }

    public function suiviKobo(SuiviKoboRequest $request)
    {
        return $this->suiviIndicateurService->suiviKobo($request->all());
    }

    /**
     *
     */
    public function valider(Request $request, $idSuiviIndicateur)
    {
        return $this->suiviIndicateurService->valider($idSuiviIndicateur);
    }
}
