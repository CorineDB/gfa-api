<?php

namespace App\Http\Controllers;

use App\Http\Requests\option_de_reponse\StoreRequest;
use App\Http\Requests\option_de_reponse\UpdateRequest;
use Core\Services\Interfaces\OptionDeReponseServiceInterface;

class OptionDeReponseController extends Controller
{
    /**
     * @var service
     */
    private $optionDeReponseService;

    /**
     * Instantiate a new OptionDeReponseController instance.
     * @param OptionDeReponseController $optionDeReponseServiceInterface
     */
    public function __construct(OptionDeReponseServiceInterface $optionDeReponseServiceInterface)
    {
        $this->middleware('permission:voir-une-option-de-reponse')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-option-de-reponse')->only(['update']);
        $this->middleware('permission:creer-une-option-de-reponse')->only(['store']);
        $this->middleware('permission:supprimer-une-option-de-reponse')->only(['destroy']);

        $this->optionDeReponseService = $optionDeReponseServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->optionDeReponseService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->optionDeReponseService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->optionDeReponseService->findById($id);
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
        return $this->optionDeReponseService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->optionDeReponseService->deleteById($id);
    }
}
