<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\type_de_gouvernance\StoreRequest;
use App\Http\Requests\type_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\TypeDeGouvernanceServiceInterface;

class TypeDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $typeDeGouvernanceService;

    /**
     * Instantiate a new TypeDeGouvernanceController instance.
     * @param TypeDeGouvernanceServiceInterface $typeDeGouvernanceServiceInterface
     */
    public function __construct(TypeDeGouvernanceServiceInterface $typeDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-type-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-type-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-type-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-type-de-gouvernance')->only(['destroy']);
        $this->middleware('permission:voir-un-principe-de-gouvernance')->only(['principes']);
        $this->typeDeGouvernanceService = $typeDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->typeDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->typeDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->typeDeGouvernanceService->findById($id);
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
        return $this->typeDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->typeDeGouvernanceService->deleteById($id);
    }

    /**
     * Principes of a type de gouvernance.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function principes($id)
    {
        return $this->typeDeGouvernanceService->principes($id);
    }
}
