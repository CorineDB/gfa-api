<?php

namespace App\Http\Controllers;

use App\Http\Requests\planDecaissement\StorePlanDecaissementRequest;
use App\Http\Requests\planDecaissement\UpdatePlanDecaissementRequest;
use Core\Services\Interfaces\PlanDecaissementServiceInterface;

class PlanDecaissementController extends Controller
{
    /**
     * @var service
     */
    private $planDecaissementService;

    /**
     * Instantiate a new PlanDecaissementController instance.
     * @param PlanDecaissmeentServiceInterface $planDecaissementService
     */
    public function __construct(PlanDecaissementServiceInterface $planDecaissementService)
    {
        $this->middleware('permission:voir-un-plan-de-decaissement')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-plan-de-decaissement')->only(['update']);
        $this->middleware('permission:creer-un-plan-de-decaissement')->only(['store']);
        $this->middleware('permission:supprimer-un-plan-de-decaissement')->only(['destroy']);

        $this->planDecaissementService = $planDecaissementService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->planDecaissementService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePlanDecaissementRequest $request)
    {
        return $this->planDecaissementService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanDecaissement  $planDecaissement
     * @return \Illuminate\Http\Response
     */
    public function show($planDecaissement)
    {
        return $this->planDecaissementService->findById($planDecaissement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PlanDecaissement  $planDecaissement
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanDecaissementRequest $request, $planDecaissement)
    {
        return $this->planDecaissementService->update($planDecaissement, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanDecaissement  $planDecaissement
     * @return \Illuminate\Http\Response
     */
    public function destroy($planDecaissement)
    {
        return $this->planDecaissementService->deleteById($planDecaissement);
    }
}
