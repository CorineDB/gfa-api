<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\questions_operationnelle\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\questions_operationnelle\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\QuestionOperationnelleServiceInterface;

class QuestionOperationnelleController extends Controller
{
    /**
     * @var service
     */
    private $questionOperationnelleService;

    /**
     * Instantiate a new QuestionOperationnelleController instance.
     * @param QuestionOperationnelleServiceInterface $questionOperationnelleServiceInterface
     */
    public function __construct(QuestionOperationnelleServiceInterface $questionOperationnelleServiceInterface)
    {
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-indicateur-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-indicateur-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-indicateur-de-gouvernance')->only(['destroy']);

        $this->questionOperationnelleService = $questionOperationnelleServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->questionOperationnelleService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->questionOperationnelleService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\QuestionOperationnelle  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->questionOperationnelleService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\QuestionOperationnelle  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->questionOperationnelleService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\QuestionOperationnelle  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->questionOperationnelleService->deleteById($id);
    }
}
