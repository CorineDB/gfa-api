<?php

namespace App\Http\Controllers;

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->surveyReponseService->update($id, $request->all());
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
}
