<?php

namespace App\Http\Controllers;

use App\Http\Requests\critere_de_gouvernance\StoreRequest;
use App\Http\Requests\critere_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\CritereDeGouvernanceServiceInterface;

class CritereDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $critereDeGouvernanceService;

    /**
     * Instantiate a new CritereDeGouvernanceController instance.
     * @param CritereDeGouvernanceServiceInterface $critereDeGouvernanceServiceInterface
     */
    public function __construct(CritereDeGouvernanceServiceInterface $critereDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-critere-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-critere-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-critere-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-critere-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['indicateurs']);
        $this->critereDeGouvernanceService = $critereDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->critereDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->critereDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->critereDeGouvernanceService->findById($id);
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
        return $this->critereDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->critereDeGouvernanceService->deleteById($id);
    }

    /**
     * Indicateurs of a critere de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicateurs($id)
    {
        return $this->critereDeGouvernanceService->indicateurs($id);
    }
}
