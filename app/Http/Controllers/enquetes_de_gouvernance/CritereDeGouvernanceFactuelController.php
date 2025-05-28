<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\criteres_de_gouvernance_factuel\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\criteres_de_gouvernance_factuel\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\CritereDeGouvernanceFactuelServiceInterface;

class CritereDeGouvernanceFactuelController extends Controller
{
    /**
     * @var service
     */
    private $critereDeGouvernanceFactuelService;

    /**
     * Instantiate a new CritereDeGouvernanceFactuelController instance.
     * @param CritereDeGouvernanceFactuelServiceInterface $critereDeGouvernanceFactuelServiceInterface
     */
    public function __construct(CritereDeGouvernanceFactuelServiceInterface $critereDeGouvernanceFactuelServiceInterface)
    {
        $this->middleware('permission:voir-un-critere-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-critere-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-critere-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-critere-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['indicateurs']);

        $this->critereDeGouvernanceFactuelService = $critereDeGouvernanceFactuelServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->critereDeGouvernanceFactuelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->critereDeGouvernanceFactuelService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->critereDeGouvernanceFactuelService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->critereDeGouvernanceFactuelService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->critereDeGouvernanceFactuelService->deleteById($id);
    }

    /**
     * Indicateurs of a principe de gouvernance.
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicateurs($id)
    {
        return $this->critereDeGouvernanceFactuelService->indicateurs($id);
    }
}
