<?php

namespace App\Http\Controllers;

use App\Http\Requests\evaluations_de_gouvernance\PerceptionSoumissionRequest;
use App\Http\Requests\evaluations_de_gouvernance\PerceptionSoumissionValidationRequest;
use App\Http\Requests\evaluations_de_gouvernance\SoumissionRequest;
use App\Http\Requests\evaluations_de_gouvernance\SoumissionValidationRequest;
use Core\Services\Interfaces\SoumissionServiceInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $this->middleware('permission:voir-une-soumission')->only(['index', 'show']);
        $this->middleware('permission:creer-une-soumission')->only(['store']);
        $this->middleware('permission:supprimer-une-soumission')->only(['destroy']);
        $this->middleware('permission:signaler-une-action-a-mener-est-realise')->only(['notifierActionAMenerEstTerminer']);
        $this->middleware('permission:valider-une-soumission')->only(['validated']);

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
        $atttributs = array_merge(["evaluationId" => $evaluationId->id, 'validation' => true], $request->all());

        return $this->soumissionService->create($atttributs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function perceptionSoumissionValidation(PerceptionSoumissionValidationRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id, 'validation' => true], $request->all());

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
    public function destroy($evaluationId, $id)
    {
        return $this->soumissionService->deleteById($id);
    }
}
