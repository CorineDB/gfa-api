<?php

namespace App\Http\Controllers;

use App\Http\Requests\esuivi\DateRequest;
use App\Http\Requests\esuivi\FormulaireRequest;
use App\Http\Requests\esuivie\StoreRequest;
use App\Http\Requests\esuivie\UpdateRequest;
use App\Http\Requests\formulaire\GrapheRequest;
use Core\Services\Interfaces\ESuiviServiceInterface;

class ESuiviController extends Controller
{
    /**
     * @var service
     */
    private $eSuiviService;

    /**
     * Instantiate a new ESuiviController instance.
     * @param CheckListComServiceInterface $eSuiviServiceInterface
     */
    public function __construct(ESuiviServiceInterface $eSuiviServiceInterface)
    {
        $this->middleware('permission:voir-un-suivi-environnementale')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-suivi-environnementale')->only(['update']);
        $this->middleware('permission:creer-un-suivi-environnementale')->only(['store']);
        $this->middleware('permission:supprimer-un-suivi-environnementale')->only(['destroy']);

        $this->eSuiviService = $eSuiviServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->eSuiviService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->eSuiviService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->eSuiviService->findById($id);
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
        return $this->eSuiviService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->eSuiviService->deleteById($id);
    }

    public function dates(DateRequest $request)
    {
        return $this->eSuiviService->dates($request->all());
    }

    public function formulaires(FormulaireRequest $request)
    {
        return $this->eSuiviService->formulaires($request->all());
    }

    public function graphes(GrapheRequest $request)
    {
        return $this->eSuiviService->graphes($request->all());
    }
}
