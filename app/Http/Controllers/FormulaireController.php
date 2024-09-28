<?php

namespace App\Http\Controllers;

use App\Http\Requests\formulaire\getSuiviRequest;
use App\Http\Requests\formulaire\GrapheRequest;
use Illuminate\Http\Request;
use App\Http\Requests\formulaire\StoreRequest;
use App\Http\Requests\formulaire\UpdateRequest;
use Core\Services\Interfaces\FormulaireServiceInterface;

class FormulaireController extends Controller
{
    private $formulaireService;

    /**
     * Instantiate a new FormulaireController instance.
     * @param CheckListComServiceInterface $formulaireServiceInterface
     */
    public function __construct(FormulaireServiceInterface $formulaireServiceInterface)
    {
        $this->middleware('permission:voir-un-formulaire')->only(['index', 'allGeneral', 'show']);
        $this->middleware('permission:modifier-un-formulaire')->only(['update']);
        $this->middleware('permission:creer-un-formulaire')->only(['store']);
        $this->middleware('permission:supprimer-un-formulaire')->only(['destroy']);

        $this->formulaireService = $formulaireServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->formulaireService->all();
    }

    public function allGeneral()
    {
        return $this->formulaireService->allGeneral();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->formulaireService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->formulaireService->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->formulaireService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->formulaireService->deleteById($id);
    }

    public function getSuivi(getSuiviRequest $request)
    {
        return $this->formulaireService->getSuivi($request->all());
    }

    public function getSuiviGeneral(getSuiviRequest $request)
    {
        return $this->formulaireService->getSuivi($request->all());
    }

    
}
