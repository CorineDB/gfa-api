<?php

namespace App\Http\Controllers;

use App\Models\Fichier;
use Illuminate\Http\Request;
use App\Http\Requests\fichier\StoreFichierRequest;
use App\Http\Requests\fichier\UpdateFichierRequest;
use Core\Services\Interfaces\FichierServiceInterface;

class FichierController extends Controller
{
    /**
     * @var service
     */
    private $fichierService;

    /**
     * Instantiate a new FichierController instance.
     * @param FichierServiceInterface $fichierServiceInterface
     */
    public function __construct(FichierServiceInterface $fichierServiceInterface)
    {
        $this->middleware('permission:voir-un-fichier')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-fichier')->only(['update']);
        $this->middleware('permission:creer-un-fichier')->only(['store']);
        $this->middleware('permission:supprimer-un-fichier')->only(['destroy']);

        $this->fichierService = $fichierServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->fichierService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFichierRequest $request)
    {
        return $this->fichierService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Fichier  $fichier
     * @return \Illuminate\Http\Response
     */
    public function show(Fichier $fichier)
    {
        return $this->fichierService->find($fichier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fichier  $fichier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $fichier)
    {
        return $this->fichierService->update($fichier, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fichier  $fichier
     * @return \Illuminate\Http\Response
     */
    public function destroy($fichier)
    {
        return $this->fichierService->deleteById($fichier);
    }
}
