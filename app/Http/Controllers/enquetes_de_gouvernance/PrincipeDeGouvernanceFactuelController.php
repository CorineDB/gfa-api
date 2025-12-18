<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\principes_de_gouvernance_factuel\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\principes_de_gouvernance_factuel\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuelServiceInterface;

class PrincipeDeGouvernanceFactuelController extends Controller
{
    /**
     * @var service
     */
    private $principeDeGouvernanceFactuelService;

    /**
     * Instantiate a new PrincipeDeGouvernanceFactuelController instance.
     * @param PrincipeDeGouvernanceFactuelServiceInterface $principeDeGouvernanceFactuelServiceInterface
     */
    public function __construct(PrincipeDeGouvernanceFactuelServiceInterface $principeDeGouvernanceFactuelServiceInterface)
    {
        $this->middleware('permission:voir-un-principe-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-principe-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-principe-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-principe-de-gouvernance')->only(['destroy']);
        $this->principeDeGouvernanceFactuelService = $principeDeGouvernanceFactuelServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->principeDeGouvernanceFactuelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->principeDeGouvernanceFactuelService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->principeDeGouvernanceFactuelService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->principeDeGouvernanceFactuelService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->principeDeGouvernanceFactuelService->deleteById($id);
    }
}
