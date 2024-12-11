<?php

namespace App\Http\Controllers;

use App\Http\Requests\principe_de_gouvernance\StoreRequest;
use App\Http\Requests\principe_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\PrincipeDeGouvernanceServiceInterface;
use Illuminate\Support\Facades\Auth;

class PrincipeDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $principeDeGouvernanceService;

    /**
     * Instantiate a new PrincipeDeGouvernanceController instance.
     * @param PrincipeDeGouvernanceServiceInterface $principeDeGouvernanceServiceInterface
     */
    public function __construct(PrincipeDeGouvernanceServiceInterface $principeDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-principe-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-principe-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-principe-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-principe-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-un-critere-de-gouvernance')->only(['criteres']);
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['indicateurs']);

        $this->principeDeGouvernanceService = $principeDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->principeDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->principeDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->principeDeGouvernanceService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->principeDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->principeDeGouvernanceService->deleteById($id);
    }

    /**
     * Criteres of a principe de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function criteres($id)
    {
        return $this->principeDeGouvernanceService->criteres($id);
    }

    /**
     * Indicateurs of a principe de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicateurs($id)
    {
        return $this->principeDeGouvernanceService->indicateurs($id);
    }

    /**
     * Formulaire de l'outil factuel
     *
     * @param  String  $progammeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function formulaire_factuel($enqueteId = null, $organisationId = null)
    {
        return $this->principeDeGouvernanceService->formulaire_factuel($enqueteId, $organisationId);
    }

    /**
     * Formulaire de l'outil de perception
     *
     * @param  String  $progammeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function formulaire_de_perception($enqueteId = null, $organisationId = null)
    {
        return $this->principeDeGouvernanceService->formulaire_de_perception($enqueteId, $organisationId);
    }
}
