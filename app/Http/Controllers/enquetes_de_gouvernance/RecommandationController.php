<?php

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\recommandations\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\recommandations\UpdateRequest;
use Core\Services\Interfaces\RecommandationServiceInterface;

class RecommandationController extends Controller
{
    /**
     * @var service
     */
    private $recommandationService;

    /**
     * Instantiate a new RecommandationController instance.
     * @param RecommandationController $recommandationServiceInterface
     */
    public function __construct(RecommandationServiceInterface $recommandationServiceInterface)
    {
        $this->middleware('permission:voir-une-recommandation')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-recommandation')->only(['update']);
        $this->middleware('permission:creer-une-recommandation')->only(['store']);
        $this->middleware('permission:supprimer-une-recommandation')->only(['destroy']);

        $this->recommandationService = $recommandationServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->recommandationService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        dd($request->all());
        return $this->recommandationService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->recommandationService->findById($id);
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
        return $this->recommandationService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->recommandationService->deleteById($id);
    }
}
