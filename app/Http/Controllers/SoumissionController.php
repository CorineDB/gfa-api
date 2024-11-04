<?php

namespace App\Http\Controllers;

use App\Http\Requests\evaluations_de_gouvernance\PerceptionSoumissionRequest;
use App\Http\Requests\evaluations_de_gouvernance\SoumissionRequest;
use App\Http\Requests\evaluations_de_gouvernance\SoumissionValidationRequest;
use App\Http\Requests\soumissions\UpdateRequest;
use Core\Services\Interfaces\SoumissionServiceInterface;
use Illuminate\Http\Request;

class SoumissionController extends Controller
{
    /**
     * @var service
     */
    private $soumissionService;

    /**
     * Instantiate a new SoumissionController instance.
     * @param SoumissionController $soumissionServiceInterface
     */
    public function __construct(SoumissionServiceInterface $soumissionServiceInterface)
    {
        $this->soumissionService = $soumissionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $evaluationId)
    {
        // Retrieve query parameters with defaults
        $columns = explode(',', request()->query('columns', '*'));

        $relations = request()->query('relations', null) ? explode(',', request()->query('relations')): []; // Default to no relations

        // Initialize filters
        $filters = request()->query('filters', []);

        if(!is_string($evaluationId) && is_object($evaluationId))
        {
            $evaluationId = $evaluationId->id;
        }
        // If filters is not an array, create a new one
        $filters = ['evaluationId' => $evaluationId];
        $filters = [];

        // Check if filters are present
        if (!empty($filters)) {
            return $this->soumissionService->allFiltredBy($filters, $columns, $relations);
        }
        return $this->soumissionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SoumissionRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id], $request->all());

        return $this->soumissionService->create($atttributs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validated(SoumissionValidationRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id], $request->all());

        return $this->soumissionService->create($atttributs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePerception(PerceptionSoumissionRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id], $request->all());

        return $this->soumissionService->create($atttributs);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($evaluationId, $soumissionId)
    {
        return $this->soumissionService->findById($soumissionId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(SoumissionRequest $request, $evaluationId, $soumissionId)
    {
        return $this->soumissionService->update($soumissionId, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->soumissionService->deleteById($id);
    }

    public function fiche_de_synthese($id)
    {
        return $this->soumissionService->fiche_de_synthese($id);
    }

    public function actions_a_mener($id)
    {
        return $this->soumissionService->actions_a_mener($id);
    }

    public function recommandations($id)
    {
        return $this->soumissionService->recommandations($id);
    }
}
