<?php

namespace App\Http\Controllers;

use App\Http\Requests\tache\StoreTacheRequest;
use App\Http\Requests\tache\UpdateTacheRequest;
use App\Http\Requests\duree\StoreDureeRequest;
use App\Http\Requests\duree\UpdateDureeRequest;
use App\Http\Requests\suivi\StoreSuiviV2Request;
use App\Http\Requests\tache\DeplacerRequest;
use App\Http\Requests\tache\ProlongementRequest;
use App\Services\SuiviService;
use Core\Services\Interfaces\TacheServiceInterface;
use Illuminate\Http\Request;

class TacheController extends Controller
{
    /**
     * @var service
     */
    private $tacheService;

    /**
     * Instantiate a new ActiviteController instance.
     * @param TacheServiceInterface $tacheServiceInterface
     */
    public function __construct(TacheServiceInterface $tacheServiceInterface)
    {
        $this->middleware('permission:voir-une-tache')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-tache')->only(['update', 'deplacer', 'ajouterDuree', 'modifierDuree', 'changeStatut']);
        $this->middleware('permission:creer-une-tache')->only(['store']);
        $this->middleware('permission:supprimer-une-tache')->only(['destroy']);
        $this->middleware('permission:voir-un-suivi')->only(['suivis']);
        $this->middleware('permission:creer-un-suivi')->only(['suivisV2']);
        $this->middleware('permission:prolonger-une-tache')->only(['prolonger']);
        

        $this->tacheService = $tacheServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->tacheService->all();
    }

    /**
     * Liste des suivis d'une tache
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suivis($id)
    {
        return $this->tacheService->suivis($id);
    }

    public function changeStatut($id)
    {
        return $this->tacheService->changeStatut($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTacheRequest $request)
    {
        return $this->tacheService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tache  $tache
     * @return \Illuminate\Http\Response
     */
    public function show($tache)
    {
        return $this->tacheService->findById($tache);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tache  $tache
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTacheRequest $request, $tache)
    {
        return $this->tacheService->update($tache, $request->all());
    }

    /**
     * Prolongement de date de fin
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function prolonger(ProlongementRequest $request, $tache)
    {
        return $this->tacheService->prolonger($tache, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tache  $tache
     * @return \Illuminate\Http\Response
     */
    public function destroy( $tache)
    {
        return $this->tacheService->deleteById($tache);
    }

    public function ajouterDuree(StoreDureeRequest $request, $id)
    {
        return $this->tacheService->ajouterDuree($request->all(), $id);
    }

    public function modifierDuree(UpdateDureeRequest $request, $tacheId, $dureeId)
    {
        return $this->tacheService->modifierDuree($request->all(), $tacheId, $dureeId);
    }

    public function deplacer(DeplacerRequest $request, $id)
    {
        return $this->tacheService->deplacer($request->all(), $id);
    }

    /**
     * Liste des suivis d'une tache
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suivisV2(StoreSuiviV2Request $request, $id)
    {
        return app(SuiviService::class)->suiviV2($request->all(), $id);
    }
    
}
