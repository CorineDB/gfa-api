<?php

namespace App\Http\Controllers;

use App\Http\Requests\membre_unitee_de_gestion\StoreRequest;
use App\Http\Requests\membre_unitee_de_gestion\UpdateRequest;
use Core\Services\Interfaces\MembreUniteeDeGestionServiceInterface;
use Illuminate\Http\Request;

class MembreUniteeGestionController extends Controller
{
    /**
     * @var service
     */
    private $membreUniteeDeGestionService;

    /**
     * Instantiate a new AuthController instance.
     * @param UniteeDeGestionServiceInterface $authServiceInterface
     */
    public function __construct(MembreUniteeDeGestionServiceInterface $membreUniteeDeGestionServiceInterface)
    {
        $this->middleware('permission:voir-une-unitee-de-gestion')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-unitee-de-gestion')->only(['update']);
        $this->middleware('permission:creer-une-unitee-de-gestion')->only(['store']);
        $this->middleware('permission:supprimer-une-unitee-de-gestion')->only(['destroy']);
        $this->membreUniteeDeGestionService = $membreUniteeDeGestionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->membreUniteeDeGestionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->membreUniteeDeGestionService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idUniteeDeGestion
     * @return \Illuminate\Http\Response
     */
    public function show($idUniteeDeGestion)
    {
        return $this->membreUniteeDeGestionService->findById($idUniteeDeGestion);
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
        return $this->membreUniteeDeGestionService->update($idUniteeDeGestion, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UniteeDeGestion  $idUniteeDeGestion
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUniteeDeGestion)
    {
        return $this->membreUniteeDeGestionService->deleteById($idUniteeDeGestion);
    }
}
