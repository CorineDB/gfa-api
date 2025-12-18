<?php

namespace App\Http\Controllers;

use App\Http\Requests\suivi_indicateur_mod\FilterRequest;
use App\Http\Requests\suivi_indicateur_mod\StoreRequest;
use App\Http\Requests\suivi_indicateur_mod\UpdateRequest;
use App\Http\Requests\suiviIndicateur\DateSuivieRequest;
use Core\Services\Interfaces\SuiviIndicateurMODServiceInterface;

class SuiviIndicateurMODController extends Controller
{
    /**
     * @var service
     */
    private $suiviIndicateurMODService;

    /**
     * Instantiate a new SuiviIndicateurMODController instance.
     * @param SuiviIndicateurMODServiceInterface $suiviIndicateurMODServiceInterface
     */
    public function __construct(SuiviIndicateurMODServiceInterface $suiviIndicateurMODServiceInterface)
    {
        $this->middleware('permission:voir-un-suivi-indicateur-mod')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-suivi-indicateur-mod')->only(['update']);
        $this->middleware('permission:creer-un-suivi-indicateur-mod')->only(['store']);
        $this->middleware('permission:supprimer-un-suivi-indicateur-mod')->only(['destroy']);

        $this->suiviIndicateurMODService = $suiviIndicateurMODServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->suiviIndicateurMODService->all();
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
        return $this->suiviIndicateurMODService->filter($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->suiviIndicateurMODService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idSuiviIndicateurMODs
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSuiviIndicateurMOD)
    {
        return $this->suiviIndicateurMODService->findById($idSuiviIndicateurMOD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idSuiviIndicateurMOD
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSuiviIndicateurMOD)
    {
        return $this->suiviIndicateurMODService->update($idSuiviIndicateurMOD, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idSuiviIndicateurMOD
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSuiviIndicateurMOD)
    {
        return $this->suiviIndicateurMODService->deleteById($idSuiviIndicateurMOD);
    }

    public function dateSuivie(DateSuivieRequest $request)
    {
        return $this->suiviIndicateurMODService->dateSuivie($request->all());
    }

}
