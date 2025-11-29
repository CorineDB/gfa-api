<?php

namespace App\Http\Controllers;

use App\Http\Requests\composante\DeplacerRequest;
use App\Http\Requests\composante\StoreComposanteRequest;
use App\Http\Requests\composante\UpdateComposanteRequest;
use Core\Services\Interfaces\ComposanteServiceInterface;

class ComposanteController extends Controller
{
   /**
     * @var service
     */
    private $composanteService;

    /**
     * Instantiate a new ProjetController instance.
     * @param ComposanteServiceInterface $composanteServiceInterface
     */
    public function __construct(ComposanteServiceInterface $composanteServiceInterface)
    {
        $this->middleware('permission:voir-un-outcome')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-outcome')->only(['update', 'deplacer']);
        $this->middleware('permission:creer-un-outcome')->only(['store']);
        $this->middleware('permission:supprimer-un-outcome')->only(['destroy']);
        $this->middleware('permission:voir-un-suivi')->only(['suivis']);
        $this->middleware('permission:voir-un-output')->only(['sousComposantes']);
        $this->middleware('permission:voir-une-activite')->only(['activites']);

        $this->composanteService = $composanteServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->composanteService->all();
    }

    /**
     * Liste des suivis d'une composante
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suivis($id)
    {
        return $this->composanteService->suivis($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreComposanteRequest $request)
    {
        dd($request->validated());
        return $this->composanteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $composante
     * @return \Illuminate\Http\Response
     */
    public function show($composante)
    {
        return $this->composanteService->findById($composante);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param $composante
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateComposanteRequest $request, $composante)
    {
        return $this->composanteService->update($composante, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $composante
     * @return \Illuminate\Http\Response
     */
    public function destroy($composante)
    {
        return $this->composanteService->deleteById($composante);
    }

    public function sousComposantes($id = null)
    {
        return $this->composanteService->sousComposantes($id);
    }

    public function activites($id)
    {
        return $this->composanteService->activites($id);
    }

    public function deplacer(DeplacerRequest $request, $id)
    {
        return $this->composanteService->deplacer($request->all(), $id);
    }
}
