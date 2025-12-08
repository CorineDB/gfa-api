<?php

namespace App\Http\Controllers;

use App\Http\Requests\projet\DecaissementParAnneeRequest;
use App\Http\Requests\projet\ProlongementRequest;
use App\Http\Requests\projet\StoreProjetRequest;
use App\Http\Requests\projet\TauxDeDecaissementRequest;
use App\Http\Requests\projet\TefRequest;
use App\Http\Requests\projet\UpdateProjetRequest;
use Core\Services\Interfaces\ProjetServiceInterface;
use Exception;

class ProjetController extends Controller
{
    /**
     * @var service
     */
    private $projetService;

    /**
     * Instantiate a new ProjetController instance.
     * @param ProjetServiceInterface $projetServiceInterface
     */
    public function __construct(ProjetServiceInterface $projetServiceInterface)
    {
        $this->middleware('permission:voir-un-projet')->only(['index', 'show', 'statistiques','tef']);
        $this->middleware('permission:voir-un-outcome')->only(['composantes']);
        $this->middleware('permission:voir-un-decaissement')->only(['decaissements']);
        $this->middleware('role:unitee-de-gestion')->only(['store','destroy']);
        $this->middleware('permission:creer-un-projet')->only(['store']);
        $this->middleware('permission:supprimer-un-projet')->only(['destroy']);
        $this->middleware('permission:modifier-un-projet')->only(['update']);
        $this->middleware('permission:prolonger-un-projet')->only(['prolonger']);


        $this->projetService = $projetServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->projetService->all();
    }

    public function statistiques($id)
    {
        return $this->projetService->statistiques($id);
    }

    public function composantes($id = null)
    {
        return $this->projetService->composantes($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjetRequest $request)
    {
        return $this->projetService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Projet  $projet
     * @return \Illuminate\Http\Response
     */
    public function show($idProjet)
    {
        return $this->projetService->findById($idProjet);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjetRequest $request, $idProjet)
    {
        return $this->projetService->update($idProjet, $request->all());
    }

    /**
     * Prolongement de date de fin
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function prolonger(ProlongementRequest $request, $idProjet)
    {
        return $this->projetService->prolonger($idProjet, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Projet  $projet
     * @return \Illuminate\Http\Response
     */
    public function destroy($idProjet)
    {
        return $this->projetService->deleteById($idProjet);
    }

    public function decaissements($id)
    {
        return $this->projetService->decaissements($id);
    }

    public function cadreLogique($id)
    {
        return $this->projetService->cadreLogique($id);
    }

    public function decaissementParAnnee(DecaissementParAnneeRequest $request, $id)
    {
        return $this->projetService->decaissementParAnnee($id, $request->all());
    }

    public function tef(TefRequest $request, $id)
    {
        return $this->projetService->tef($id, $request->all());
    }

    public function mesure_rendement($id)
    {
        return $this->projetService->mesure_rendement($id);
    }
}
