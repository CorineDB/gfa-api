<?php

namespace App\Http\Controllers;

use App\Http\Requests\bailleur\StoreRequest;
use App\Http\Requests\bailleur\UpdateRequest;
use Core\Services\Interfaces\BailleurServiceInterface;
use Illuminate\Http\Request;

class BailleurController extends Controller
{
    /**
     * @var service
     */
    private $bailleurService;

    /**
     * Instantiate a new AuthController instance.
     * @param BailleurServiceInterface $authServiceInterface
     */
    public function __construct(BailleurServiceInterface $bailleurServiceInterface)
    {
        $this->middleware('permission:voir-un-bailleur')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-bailleur')->only(['update']);
        $this->middleware('permission:creer-un-bailleur')->only(['store']);
        $this->middleware('permission:supprimer-un-bailleur')->only(['destroy']);

        $this->bailleurService = $bailleurServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->bailleurService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->bailleurService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idBailleur
     * @return \Illuminate\Http\Response
     */
    public function show($idBailleur)
    {
        return $this->bailleurService->findById($idBailleur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idBailleur
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idBailleur)
    {
        return $this->bailleurService->update($idBailleur, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bailleur  $idBailleur
     * @return \Illuminate\Http\Response
     */
    public function destroy($idBailleur)
    {
        return $this->bailleurService->deleteById($idBailleur);
    }

    public function anos()
    {
        return $this->bailleurService->anos();
    }

    public function entreprisesExecutant()
    {
        return $this->bailleurService->entreprisesExecutant();
    }

    public function indicateurs()
    {
        return $this->bailleurService->indicateurs();
    }

    public function suiviIndicateurs()
    {
        return $this->bailleurService->suiviIndicateurs();
    }

}
