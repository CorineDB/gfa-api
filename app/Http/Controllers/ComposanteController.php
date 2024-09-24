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
        $this->middleware('permission:voir-une-composante')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-composante')->only(['update']);
        $this->middleware('permission:creer-une-composante')->only(['store']);
        $this->middleware('permission:supprimer-une-composante')->only(['destroy']);

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
