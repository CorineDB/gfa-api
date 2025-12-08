<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur_mod\StoreRequest;
use App\Http\Requests\indicateur_mod\UpdateRequest;
use App\Http\Requests\suivi_indicateur_mod\FilterRequest;
use Core\Services\Interfaces\IndicateurModServiceInterface;
use Illuminate\Support\Facades\Auth;

class IndicateurModController extends Controller
{
    /**
     * @var service
     */
    private $indicateurModService;

    /**
     * Instantiate a new IndicateurModController instance.
     * @param IndicateurServiceInterface $indicateurModServiceInterface
     */
    public function __construct(IndicateurModServiceInterface $indicateurModServiceInterface)
    {
        $this->middleware('permission:voir-un-indicateur-mod')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-indicateur-mod')->only(['update']);
        $this->middleware('permission:creer-un-indicateur-mod')->only(['store']);
        $this->middleware('permission:supprimer-un-indicateur-mod')->only(['destroy']);

        $this->indicateurModService = $indicateurModServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->indicateurModService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        //Je verifie si c'est un mod qui est entrain de créer l'indicateur si oui je recupere son id sinon je recupère des parametres
        $request["modId"] = (array_key_exists("modId", $request->all()) && isset($request["modId"])) ? $request["modId"] : Auth::user()->mod->id;

        return $this->indicateurModService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idIndicateur)
    {
        return $this->indicateurModService->findById($idIndicateur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idIndicateur)
    {
        $request["modId"] = (array_key_exists("modId", $request->all()) && isset($request["modId"])) ? $request["modId"] : Auth::user()->mod->id;

        return $this->indicateurModService->update($idIndicateur, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idIndicateur)
    {
        return $this->indicateurModService->deleteById($idIndicateur);
    }

    /**
     * Check if indicateur has suivi for a specifique year
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSuivi($idIndicateur, $year)
    {
        return $this->indicateurModService->checkSuivi($idIndicateur, $year);
    }

    public function suivis($id){

        return $this->indicateurModService->suivis($id);
    }

    public function filtre(){

        return [];
    }

}
