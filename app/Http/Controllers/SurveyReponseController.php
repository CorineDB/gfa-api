<?php

namespace App\Http\Controllers;

use App\Http\Requests\surveys\reponses\PublicStoreRequest;
use App\Http\Requests\surveys\reponses\StoreRequest;
use App\Http\Requests\surveys\reponses\UpdateRequest;
use Core\Services\Interfaces\SurveyReponseServiceInterface;

class SurveyReponseController extends Controller
{
    /**
     * @var service
     */
    private $surveyReponseService;

    /**
     * Instantiate a new SurveyController instance.
     * @param SurveyReponseController $surveyReponseServiceInterface
     */
    public function __construct(SurveyReponseServiceInterface $surveyReponseServiceInterface)
    {
        $this->middleware('permission:voir-reponses-enquete-individuelle')->only(['index', 'show']);
        $this->middleware('permission:supprimer-une-reponse-enquete-individuelle')->only(['destroy']);
        
        $this->surveyReponseService = $surveyReponseServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->surveyReponseService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->surveyReponseService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->surveyReponseService->findById($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->surveyReponseService->deleteById($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function survey_reponse(PublicStoreRequest $request)
    {
        return $this->surveyReponseService->create($request->all());
    }

}
