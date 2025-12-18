<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\principes_de_gouvernance_de_perception\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\principes_de_gouvernance_de_perception\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\PrincipeDeGouvernancePerceptionServiceInterface;

class PrincipeDeGouvernancePerceptionController extends Controller
{
    /**
     * @var service
     */
    private $principeDeGouvernancePerceptionService;

    /**
     * Instantiate a new PrincipeDeGouvernancePerceptionController instance.
     * @param PrincipeDeGouvernancePerceptionServiceInterface $principeDeGouvernancePerceptionServiceInterface
     */
    public function __construct(PrincipeDeGouvernancePerceptionServiceInterface $principeDeGouvernancePerceptionServiceInterface)
    {
        $this->middleware('permission:voir-un-principe-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-principe-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-principe-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-principe-de-gouvernance')->only(['destroy']);
        $this->principeDeGouvernancePerceptionService = $principeDeGouvernancePerceptionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->principeDeGouvernancePerceptionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->principeDeGouvernancePerceptionService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->principeDeGouvernancePerceptionService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->principeDeGouvernancePerceptionService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->principeDeGouvernancePerceptionService->deleteById($id);
    }
}
