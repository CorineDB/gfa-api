<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\indicateurs_de_gouvernance_factuel\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\indicateurs_de_gouvernance_factuel\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuelServiceInterface;

class IndicateurDeGouvernanceFactuelController extends Controller
{
    /**
     * @var service
     */
    private $indicateurDeGouvernanceFactuelService;

    /**
     * Instantiate a new IndicateurDeGouvernanceFactuelController instance.
     * @param IndicateurDeGouvernanceFactuelServiceInterface $indicateurDeGouvernanceFactuelServiceInterface
     */
    public function __construct(IndicateurDeGouvernanceFactuelServiceInterface $indicateurDeGouvernanceFactuelServiceInterface)
    {
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-indicateur-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-indicateur-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-indicateur-de-gouvernance')->only(['destroy']);

        $this->indicateurDeGouvernanceFactuelService = $indicateurDeGouvernanceFactuelServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->indicateurDeGouvernanceFactuelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->indicateurDeGouvernanceFactuelService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->indicateurDeGouvernanceFactuelService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->indicateurDeGouvernanceFactuelService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->indicateurDeGouvernanceFactuelService->deleteById($id);
    }
}
