<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\type_de_gouvernance_factuel\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\type_de_gouvernance_factuel\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\TypeDeGouvernanceFactuelServiceInterface;

class TypeDeGouvernanceFactuelController extends Controller
{
    /**
     * @var service
     */
    private $typeDeGouvernanceFactuelService;

    /**
     * Instantiate a new TypeDeGouvernanceFactuelController instance.
     * @param TypeDeGouvernanceFactuelServiceInterface $typeDeGouvernanceFactuelServiceInterface
     */
    public function __construct(TypeDeGouvernanceFactuelServiceInterface $typeDeGouvernanceFactuelServiceInterface)
    {
        $this->middleware('permission:voir-un-type-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-type-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-type-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-type-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-un-principe-de-gouvernance')->only(['principes']);
        $this->typeDeGouvernanceFactuelService = $typeDeGouvernanceFactuelServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->typeDeGouvernanceFactuelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->typeDeGouvernanceFactuelService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->typeDeGouvernanceFactuelService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->typeDeGouvernanceFactuelService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->typeDeGouvernanceFactuelService->deleteById($id);
    }

    /**
     * Principes of a type de gouvernance.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function principes($id)
    {
        return $this->typeDeGouvernanceFactuelService->principes($id);
    }
}
