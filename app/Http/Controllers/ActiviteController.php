<?php

namespace App\Http\Controllers;

use App\Http\Requests\activite\FiltreActiviteRequest;
use App\Http\Requests\activite\StoreActiviteRequest;
use App\Http\Requests\activite\UpdateActiviteRequest;
use App\Http\Requests\activite\DeplacerRequest;
use App\Http\Requests\activite\PpmRequest;
use App\Http\Requests\duree\StoreDureeRequest;
use App\Http\Requests\duree\UpdateDureeRequest;
use Core\Services\Interfaces\ActiviteServiceInterface;

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
        $this->middleware('permission:voir-une-activite')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-activite')->only(['update']);
        $this->middleware('permission:creer-une-activite')->only(['store']);
        $this->middleware('permission:supprimer-une-activite')->only(['destroy']);

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

    public function ajouterDuree(StoreDureeRequest $request, $id)
    {
        return $this->activiteService->ajouterDuree($request->all(),$id );
    }

    public function modifierDuree(UpdateDureeRequest $request, $dureeId)
    {
        return $this->activiteService->modifierDuree($request->all(), $dureeId);
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
