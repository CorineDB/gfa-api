<?php

namespace App\Http\Controllers;

use App\Http\Requests\surveys\forms\StoreRequest;
use App\Http\Requests\surveys\forms\UpdateRequest;
use Core\Services\Interfaces\SurveyFormServiceInterface;

class SurveyFormController extends Controller
{
    /**
     * @var service
     */
    private $surveyFormService;

    /**
     * Instantiate a new SurveyFormController instance.
     * @param SurveyFormController $surveyFormServiceInterface
     */
    public function __construct(SurveyFormServiceInterface $surveyFormServiceInterface)
    {
        $this->middleware('permission:voir-un-formulaire-individuel')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-formulaire-individuel')->only(['update']);
        $this->middleware('permission:creer-un-formulaire-individuel')->only(['store']);
        $this->middleware('permission:supprimer-un-formulaire-individuel')->only(['destroy']);
        
        $this->surveyFormService = $surveyFormServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->surveyFormService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->surveyFormService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->surveyFormService->findById($id);
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
        return $this->surveyFormService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->surveyFormService->deleteById($id);
    }
}
