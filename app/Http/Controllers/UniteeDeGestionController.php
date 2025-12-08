<?php

namespace App\Http\Controllers;

use App\Http\Requests\unitee_de_gestion\StoreRequest;
use App\Http\Requests\unitee_de_gestion\UpdateRequest;
use Core\Services\Interfaces\UniteeDeGestionServiceInterface;
use Illuminate\Http\Request;

class UniteeDeGestionController extends Controller
{
    /**
     * @var service
     */
    private $uniteeDeGestionService;

    /**
     * Instantiate a new AuthController instance.
     * @param UniteeDeGestionServiceInterface $authServiceInterface
     */
    public function __construct(UniteeDeGestionServiceInterface $uniteeDeGestionServiceInterface)
    {
        $this->middleware('permission:voir-une-unitee-de-gestion')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-unitee-de-gestion')->only(['update']);
        $this->middleware('permission:creer-une-unitee-de-gestion')->only(['store']);
        $this->middleware('permission:supprimer-une-unitee-de-gestion')->only(['destroy']);

        $this->uniteeDeGestionService = $uniteeDeGestionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->uniteeDeGestionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->uniteeDeGestionService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idUniteeDeGestion
     * @return \Illuminate\Http\Response
     */
    public function show($idUniteeDeGestion)
    {
        return $this->uniteeDeGestionService->findById($idUniteeDeGestion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idUniteeDeGestion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idUniteeDeGestion)
    {
        return $this->uniteeDeGestionService->update($idUniteeDeGestion, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UniteeDeGestion  $idUniteeDeGestion
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUniteeDeGestion)
    {
        return $this->uniteeDeGestionService->deleteById($idUniteeDeGestion);
    }

}
