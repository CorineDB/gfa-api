<?php

namespace App\Http\Controllers;

use App\Http\Requests\surveys\StoreRequest;
use App\Http\Requests\surveys\UpdateRequest;
use Core\Services\Interfaces\SurveyServiceInterface;

class SurveyController extends Controller
{
    /**
     * @var service
     */
    private $surveyService;

    /**
     * Instantiate a new SurveyController instance.
     * @param SurveyController $surveyServiceInterface
     */
    public function __construct(SurveyServiceInterface $surveyServiceInterface)
    {
        $this->middleware('permission:voir-une-enquete-individuelle')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-enquete-individuelle')->only(['update']);
        $this->middleware('permission:creer-une-enquete-individuelle')->only(['store']);
        $this->middleware('permission:supprimer-une-enquete-individuelle')->only(['destroy']);
        $this->middleware('permission:voir-un-formulaire-individuel')->only(['formulaire', 'private_survey_form']);
        $this->middleware('permission:voir-reponses-enquete-individuelle')->only(['survey_reponses']);

        $this->surveyService = $surveyServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->surveyService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->surveyService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->surveyService->findById($id);
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
        dd($id);
        return $this->surveyService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->surveyService->deleteById($id);
    }

    public function survey_reponses($id)
    {
        return $this->surveyService->survey_reponses($id);
    }

    public function formulaire($id)
    {
        return $this->surveyService->formulaire($id);
    }

    public function private_survey_form($token, $idParticipant)
    {
        return $this->surveyService->survey_form($token, $idParticipant);
    }

    public function public_survey_form($token, $idParticipant)
    {
        return $this->surveyService->survey_form($token, $idParticipant);
    }
}
